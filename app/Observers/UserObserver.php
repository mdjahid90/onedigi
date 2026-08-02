<?php

namespace App\Observers;

use App\Models\User;
use App\Services\AnalyticsService;

class UserObserver
{
    public function created(User $user): void
    {
        $request = request();

        AnalyticsService::record('account_created', [
            'user_id' => $user->id,
            'subject_type' => 'user',
            'subject_id' => $user->id,
            'ip_address' => $request?->ip(),
            'session_hash' => $this->sessionHash(),
            'meta' => [
                'actor_id' => auth()->id(),
                'is_admin' => (bool) $user->is_admin,
            ],
        ]);
    }

    public function deleted(User $user): void
    {
        $request = request();

        AnalyticsService::record('account_deleted', [
            'user_id' => auth()->id(),
            'subject_type' => 'user',
            'subject_id' => $user->id,
            'ip_address' => $request?->ip(),
            'session_hash' => $this->sessionHash(),
            'meta' => [
                'deleted_email' => $user->email,
                'actor_id' => auth()->id(),
            ],
        ]);
    }

    private function sessionHash(): ?string
    {
        $request = request();

        if (!$request || !$request->hasSession()) {
            return null;
        }

        $session = $request->session();

        return $session->isStarted()
            ? hash('sha256', (string) $session->getId())
            : null;
    }
}
