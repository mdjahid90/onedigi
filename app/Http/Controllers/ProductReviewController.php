<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $reviewsEnabled = Setting::getValue('reviews_enabled', '1') === '1';
        if (!$reviewsEnabled) {
            return back()->with('warning', 'Reviews are currently disabled.');
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'review' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        if (auth()->check()) {
            $reviewerName = (string) (auth()->user()->name ?? 'Customer');
            $reviewerEmail = (string) (auth()->user()->email ?? '');
            $reviewerEmail = $reviewerEmail !== '' ? $reviewerEmail : null;
        } else {
            $guest = $request->validate([
            'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:190'],
            ]);

            $reviewerName = $guest['name'];
            $reviewerEmail = $guest['email'];
        }

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'name' => $reviewerName,
            'email' => $reviewerEmail,
            'rating' => (int) $validated['rating'],
            'review' => trim($validated['review']),
            'status' => ProductReview::STATUS_PENDING,
        ]);

        return back()->with('success', 'Thanks for your review. It will be published after admin approval.');
    }
}
