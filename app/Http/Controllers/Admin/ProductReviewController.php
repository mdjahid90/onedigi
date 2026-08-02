<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'pending');
        $allowedStatuses = [
            'all',
            ProductReview::STATUS_PENDING,
            ProductReview::STATUS_APPROVED,
            ProductReview::STATUS_REJECTED,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = ProductReview::STATUS_PENDING;
        }

        $reviews = ProductReview::query()
            ->with(['product:id,title,slug', 'user:id,name', 'approvedByUser:id,name'])
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'activeStatus' => $status,
            'counts' => [
                'all' => ProductReview::query()->count(),
                ProductReview::STATUS_PENDING => ProductReview::query()->where('status', ProductReview::STATUS_PENDING)->count(),
                ProductReview::STATUS_APPROVED => ProductReview::query()->where('status', ProductReview::STATUS_APPROVED)->count(),
                ProductReview::STATUS_REJECTED => ProductReview::query()->where('status', ProductReview::STATUS_REJECTED)->count(),
            ],
        ]);
    }

    public function approve(ProductReview $review): RedirectResponse
    {
        $review->update([
            'status' => ProductReview::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Review approved successfully.');
    }

    public function reject(ProductReview $review): RedirectResponse
    {
        $review->update([
            'status' => ProductReview::STATUS_REJECTED,
            'approved_at' => null,
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Review rejected successfully.');
    }
}
