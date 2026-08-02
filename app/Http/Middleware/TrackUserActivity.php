<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();
        if (!$user || !Schema::hasColumn('users', 'last_seen_at')) {
            return $response;
        }

        $session = $request->hasSession() ? $request->session() : null;
        $key = 'last_seen_touched_at';
        $lastTouched = $session?->get($key);

        if ($lastTouched && now()->diffInMinutes($lastTouched) < 5) {
            return $response;
        }

        try {
            $user->forceFill([
                'last_seen_at' => now(),
                'last_seen_ip' => $request->ip(),
                'last_seen_user_agent' => substr((string) $request->userAgent(), 0, 255),
            ])->save();

            $session?->put($key, now());
        } catch (QueryException $exception) {
            report($exception);
        }

        return $response;
    }
}
