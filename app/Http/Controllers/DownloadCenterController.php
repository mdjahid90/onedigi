<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DownloadCenterController extends Controller
{
    public function __invoke(Request $request): View
    {
        $downloads = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'DELIVERED')
            ->whereHas('delivery', function ($query) {
                $query->whereNotNull('file_path')
                    ->orWhereNotNull('delivery_link');
            })
            ->with('delivery')
            ->latest()
            ->paginate(12);

        return view('pages.downloads', [
            'downloads' => $downloads,
        ]);
    }
}
