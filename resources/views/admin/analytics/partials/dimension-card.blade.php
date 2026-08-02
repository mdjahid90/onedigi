<div class="card h-100">
    <div class="card-header">
        <h3 class="card-title">{{ $title }}</h3>
    </div>
    <div class="card-body">
        @forelse($rows as $row)
            <div class="mb-3">
                <div class="d-flex justify-content-between gap-3 mb-1">
                    <span class="text-truncate fw-semibold">{{ ucfirst((string) $row['label']) }}</span>
                    <span class="text-secondary">{{ number_format((int) $row['total']) }}</span>
                </div>
                <div class="progress progress-sm">
                    <div class="progress-bar bg-primary" style="width: {{ min(100, max(4, (float) $row['percent'])) }}%"></div>
                </div>
            </div>
        @empty
            <div class="empty py-4">
                <p class="empty-title">No data</p>
                <p class="empty-subtitle text-secondary">Nothing tracked for this range.</p>
            </div>
        @endforelse
    </div>
</div>
