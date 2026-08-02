<?php

namespace App\Http\Middleware;

use App\Models\DailyVisit;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackDailyVisits
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->isMethod('GET')) {
            return $response;
        }

        if ($request->expectsJson()) {
            return $response;
        }

        if ($request->is('admin*') || $request->is('api*')) {
            return $response;
        }

        $session = $request->session();
        $date = now()->toDateString();
        $sessionKey = 'daily_visit_recorded_'.$date;

        if (!Schema::hasTable('daily_visits')) {
            return $response;
        }

        if (!$session->has($sessionKey)) {
            try {
                $visit = DailyVisit::query()->firstOrCreate(
                    ['date' => $date],
                    ['visitors' => 0]
                );

                $visit->increment('visitors');
                $session->put($sessionKey, true);
            } catch (QueryException $exception) {
                report($exception);
            }
        }

        return $response;
    }
}
