<?php

namespace App\Services;

use App\Models\AdminNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class AdminNotificationService
{
    public static function create(string $type, string $title, ?string $body = null, ?string $url = null, string $severity = 'info', ?Model $notifiable = null): void
    {
        if (!Schema::hasTable('admin_notifications')) {
            return;
        }

        try {
            AdminNotification::query()->create([
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'severity' => $severity,
                'notifiable_type' => $notifiable ? $notifiable::class : null,
                'notifiable_id' => $notifiable?->getKey(),
            ]);
        } catch (QueryException $exception) {
            report($exception);
        }
    }
}
