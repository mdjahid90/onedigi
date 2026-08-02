<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $reviewsEnabled = Setting::getValue('reviews_enabled', '1') === '1';

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(12)
            ->get();

        $trendingProducts = Product::query()
            ->with('category')
            ->withMin(['variants as min_variant_price' => function ($q) {
                $q->where('is_active', true);
            }], 'price')
            ->withMax(['variants as max_variant_price' => function ($q) {
                $q->where('is_active', true);
            }], 'price')
            ->withAvg('approvedReviews as reviews_avg_rating', 'rating')
            ->withCount('approvedReviews as reviews_count')
            ->where('is_active', true)
            ->latest()
            ->limit(12)
            ->get();

        $banners = Banner::query()
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('pages.home', [
            'categories' => $categories,
            'trendingProducts' => $trendingProducts,
            'banners' => $banners,
            'reviewsEnabled' => $reviewsEnabled,
        ]);
    }
}
