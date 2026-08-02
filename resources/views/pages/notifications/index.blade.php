<x-user-dashboard-layout title="Notifications" pretitle="Account">
    <div class="page-body">
        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase fw-semibold">Total</div>
                        <div class="h2 mb-0">{{ number_format((int) $totalNotifications) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase fw-semibold">Unread</div>
                        <div class="h2 mb-0 text-primary">{{ number_format((int) $unreadNotifications) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="card card-sm">
                    <div class="card-body">
                        <div class="text-secondary text-uppercase fw-semibold">Read</div>
                        <div class="h2 mb-0">{{ number_format((int) $readNotifications) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body border-bottom py-3">
                <div class="row g-3 align-items-center">
                    <div class="col-md-auto">
                        <div class="btn-list">
                            @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $key => $label)
                                <a href="{{ route('notifications.index', array_filter(['status' => $key, 'search' => $search])) }}" class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    <div class="col-md ms-md-auto">
                        <form method="GET" action="{{ route('notifications.index') }}" class="input-group">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search notifications">
                            <button class="btn btn-primary" type="submit">Search</button>
                            @if($search !== '')
                                <a href="{{ route('notifications.index', ['status' => $status]) }}" class="btn btn-outline-secondary">Clear</a>
                            @endif
                        </form>
                    </div>
                    <div class="col-md-auto">
                        <form method="POST" action="{{ route('notifications.read_all') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary" @disabled($unreadNotifications < 1)>
                                Mark all as read
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="list-group list-group-flush">
                @forelse($notifications as $notification)
                    @php
                        $notificationUnread = $notification->read_at === null;
                        $notificationSeverity = match ($notification->severity) {
                            'danger' => 'danger',
                            'warning' => 'warning',
                            'success' => 'success',
                            default => 'primary',
                        };
                    @endphp
                    <div class="list-group-item {{ $notificationUnread ? 'bg-primary-lt' : '' }}">
                        <div class="row align-items-center g-3">
                            <div class="col-auto">
                                <span class="status-dot {{ $notificationUnread ? 'status-dot-animated bg-' . $notificationSeverity : 'bg-secondary' }}"></span>
                            </div>
                            <div class="col">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div class="fw-semibold">{{ $notification->title }}</div>
                                    @if($notificationUnread)
                                        <span class="badge bg-primary-lt">Unread</span>
                                    @endif
                                    <span class="text-secondary small">{{ $notification->created_at?->diffForHumans() }}</span>
                                </div>
                                @if($notification->body)
                                    <div class="text-secondary mt-1">{{ $notification->body }}</div>
                                @endif
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('notifications.open', $notification) }}" class="btn btn-sm btn-outline-secondary">
                                    Open
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty">
                        <p class="empty-title">No notifications found</p>
                        <p class="empty-subtitle text-secondary">Order updates, support replies, deliveries, and refund updates will appear here.</p>
                    </div>
                @endforelse
            </div>

            @if($notifications->hasPages())
                <div class="card-footer d-flex justify-content-center justify-content-sm-end">
                    {{ $notifications->onEachSide(1)->links() }}
                </div>
            @endif
        </div>
    </div>
</x-user-dashboard-layout>
