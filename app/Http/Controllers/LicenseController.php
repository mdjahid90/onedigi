<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseController extends Controller
{
    public function __invoke(Request $request): View
    {
        $licenses = OrderItem::query()
            ->where(function ($query) {
                $query->whereNotNull('license_key')
                    ->orWhereNotNull('access_email')
                    ->orWhereNotNull('access_password');
            })
            ->whereHas('order', function ($query) use ($request) {
                $query->where('user_id', $request->user()->id)
                    ->where('status', 'DELIVERED');
            })
            ->with(['order', 'product'])
            ->latest()
            ->paginate(12);

        return view('pages.account.licenses', [
            'licenses' => $licenses,
        ]);
    }
}
