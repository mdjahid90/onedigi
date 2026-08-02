<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function suggest(Request $request): JsonResponse
    {
        $q = trim($request->string('q')->toString());

        if (mb_strlen($q) < 1) {
            return response()->json([
                'products' => [],
            ]);
        }

        $products = Product::query()
            ->with('category:id,name')
            ->where('is_active', true)
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            })
            ->latest()
            ->limit(8)
            ->get(['id', 'title', 'slug', 'price', 'thumbnail_path', 'image', 'category_id']);

        return response()->json([
            'products' => $products->map(function (Product $product) {
                $thumb = $product->thumbnail_path ?: $product->image;

                return [
                    'title' => $product->title,
                    'slug' => $product->slug,
                    'url' => route('products.show', $product),
                    'category' => $product->category?->name,
                    'price' => (float) $product->price,
                    'image' => $thumb ? Storage::url($thumb) : null,
                ];
            })->values(),
        ]);
    }

    public function index(Request $request): View
    {
        $reviewsEnabled = Setting::getValue('reviews_enabled', '1') === '1';

        $productsQuery = Product::query()
            ->with('category')
            ->withMin(['variants as min_variant_price' => function ($q) {
                $q->where('is_active', true);
            }], 'price')
            ->withMax(['variants as max_variant_price' => function ($q) {
                $q->where('is_active', true);
            }], 'price')
            ->withAvg('approvedReviews as reviews_avg_rating', 'rating')
            ->withCount('approvedReviews as reviews_count')
            ->where('is_active', true);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $activeCategory = null;
        $categorySlug = $request->string('category')->toString();
        if ($categorySlug !== '') {
            $activeCategory = Category::query()->where('slug', $categorySlug)->first();
            if ($activeCategory) {
                $productsQuery->where('category_id', $activeCategory->id);
            }
        }

        $q = trim($request->string('q')->toString());
        if ($q !== '') {
            $productsQuery->where(function ($query) use ($q) {
                $query->where('title', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            });
        }

        $minPriceRaw = $request->input('min_price');
        $maxPriceRaw = $request->input('max_price');
        $minPrice = is_numeric($minPriceRaw) ? (float) $minPriceRaw : null;
        $maxPrice = is_numeric($maxPriceRaw) ? (float) $maxPriceRaw : null;

        if ($minPrice !== null && $maxPrice !== null && $minPrice > $maxPrice) {
            [$minPrice, $maxPrice] = [$maxPrice, $minPrice];
        }

        if ($minPrice !== null) {
            $productsQuery->where('price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $productsQuery->where('price', '<=', $maxPrice);
        }

        $sort = $request->string('sort')->toString();
        if ($sort === 'price_asc') {
            $productsQuery->orderByRaw('COALESCE(min_variant_price, price) asc');
        } elseif ($sort === 'price_desc') {
            $productsQuery->orderByRaw('COALESCE(max_variant_price, price) desc');
        } else {
            $productsQuery->latest();
        }

        $products = $productsQuery->paginate(20)->withQueryString();

        return view('pages.products-index', [
            'products' => $products,
            'activeCategory' => $activeCategory,
            'categories' => $categories,
            'reviewsEnabled' => $reviewsEnabled,
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->is_active, 404);
        $reviewsEnabled = Setting::getValue('reviews_enabled', '1') === '1';

        $product->load([
            'category',
            'optionGroups.values' => function ($q) {
                $q->orderBy('sort_order');
            },
            'variants',
            'formFields',
            'approvedReviews' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);

        $product->loadAvg('approvedReviews as reviews_avg_rating', 'rating');
        $product->loadCount('approvedReviews as reviews_count');

        return view('pages.product-show', [
            'product' => $product,
            'reviewsEnabled' => $reviewsEnabled,
        ]);
    }
}
