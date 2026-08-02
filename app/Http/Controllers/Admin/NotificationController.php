<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['all', 'unread', 'read'], true)
            ? $request->query('status')
            : 'all';
        $search = trim((string) $request->query('search', ''));

        $notifications = AdminNotification::query()
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'status' => $status,
            'search' => $search,
            'totalNotifications' => AdminNotification::query()->count(),
            'unreadNotifications' => AdminNotification::query()->whereNull('read_at')->count(),
            'readNotifications' => AdminNotification::query()->whereNotNull('read_at')->count(),
        ]);
    }

    public function readAll(): RedirectResponse
    {
        AdminNotification::query()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function open(AdminNotification $notification): RedirectResponse
    {
        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        $target = $notification->url;

        if (!$target || $target === '#') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->to($target);
    }
}
