<?php

namespace App\Http\Middleware;

use App\Models\Page;
use App\Models\Product;
use App\Services\AnalyticsService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalyticsEvents
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$request->isMethod('GET')) {
            return $response;
        }

        if ($request->expectsJson() || $request->ajax()) {
            return $response;
        }

        if ($request->is('admin*') || $request->is('api*') || $response->getStatusCode() >= 400) {
            return $response;
        }

        $route = $request->route();
        $routeName = $route?->getName();

        $product = $request->route('product');
        $page = $request->route('page');

        $subject = ['subject_type' => null, 'subject_id' => null];
        if ($product instanceof Product) {
            $subject = AnalyticsService::subjectFromModel($product);
        } elseif ($page instanceof Page) {
            $subject = AnalyticsService::subjectFromModel($page);
        }

        AnalyticsService::record('page_view', [
            'user_id' => $request->user()?->id,
            'route_name' => $routeName,
            'path' => '/'.$request->path(),
            'referrer' => $request->headers->get('referer'),
            'session_hash' => $request->hasSession() ? hash('sha256', (string) $request->session()->getId()) : null,
            'ip_address' => $request->ip(),
            'subject_type' => $subject['subject_type'],
            'subject_id' => $subject['subject_id'],
            ...AnalyticsService::dimensionsFromRequest($request),
            'meta' => [
                'locale' => app()->getLocale(),
                'currency' => session('currency'),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ],
        ]);

        return $response;
    }
}
