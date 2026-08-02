<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;
        $status = in_array($request->query('status'), ['all', 'unread', 'read'], true)
            ? $request->query('status')
            : 'all';
        $search = trim((string) $request->query('search', ''));

        $baseQuery = UserNotification::query()->where('user_id', $userId);

        $notifications = (clone $baseQuery)
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

        return view('pages.notifications.index', [
            'notifications' => $notifications,
            'status' => $status,
            'search' => $search,
            'totalNotifications' => (clone $baseQuery)->count(),
            'unreadNotifications' => (clone $baseQuery)->whereNull('read_at')->count(),
            'readNotifications' => (clone $baseQuery)->whereNotNull('read_at')->count(),
        ]);
    }

    public function readAll(Request $request): RedirectResponse
    {
        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function open(Request $request, UserNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);

        if ($notification->read_at === null) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        $target = $notification->url;

        if (!$target || $target === '#') {
            return redirect()->route('dashboard');
        }

        return redirect()->to($target);
    }
}
