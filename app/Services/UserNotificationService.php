<?php

namespace App\Services;

use App\Models\UserNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class UserNotificationService
{
    public static function create(int|string|null $userId, string $type, string $title, ?string $body = null, ?string $url = null, string $severity = 'info', ?Model $notifiable = null): void
    {
        $userId = (int) $userId;

        if ($userId <= 0 || !Schema::hasTable('user_notifications')) {
            return;
        }

        try {
            UserNotification::query()->create([
                'user_id' => $userId,
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
