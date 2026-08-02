<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AnalyticsService
{
    private static ?bool $tableReady = null;

    public static function record(string $eventType, array $data = []): void
    {
        if (!self::isTableReady()) {
            return;
        }

        try {
            AnalyticsEvent::query()->create([
                'event_type' => $eventType,
                'occurred_at' => $data['occurred_at'] ?? now(),
                'user_id' => $data['user_id'] ?? null,
                'subject_type' => $data['subject_type'] ?? null,
                'subject_id' => $data['subject_id'] ?? null,
                'route_name' => $data['route_name'] ?? null,
                'path' => $data['path'] ?? null,
                'referrer' => $data['referrer'] ?? null,
                'session_hash' => $data['session_hash'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'country_code' => $data['country_code'] ?? null,
                'country_name' => $data['country_name'] ?? null,
                'device_type' => $data['device_type'] ?? null,
                'browser' => $data['browser'] ?? null,
                'source' => $data['source'] ?? null,
                'meta' => $data['meta'] ?? null,
            ]);
        } catch (QueryException $exception) {
            report($exception);
        }
    }

    public static function dimensionsFromRequest(Request $request): array
    {
        $userAgent = (string) $request->userAgent();
        $referrer = (string) $request->headers->get('referer', '');
        $countryCode = self::countryCodeFromHeaders($request);
        $geoLocation = $countryCode
            ? ['country_code' => $countryCode, 'country_name' => GeoLocationService::countryName($countryCode)]
            : (GeoLocationService::locate($request->ip()) ?? []);

        return [
            'country_code' => $geoLocation['country_code'] ?? null,
            'country_name' => $geoLocation['country_name'] ?? null,
            'device_type' => self::deviceType($userAgent),
            'browser' => self::browserName($userAgent),
            'source' => self::trafficSource($referrer),
        ];
    }

    private static function countryCodeFromHeaders(Request $request): ?string
    {
        $headers = [
            'cf-ipcountry',
            'x-vercel-ip-country',
            'x-appengine-country',
            'cloudfront-viewer-country',
            'x-country-code',
            'x-geo-country',
            'x-client-country',
            'x-forwarded-country',
        ];

        foreach ($headers as $header) {
            $value = strtoupper(trim((string) $request->headers->get($header, '')));

            if (strlen($value) === 2 && !in_array($value, ['XX', 'T1'], true)) {
                return $value;
            }
        }

        return null;
    }

    private static function deviceType(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (str_contains($ua, 'mobi') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private static function browserName(string $userAgent): string
    {
        $ua = strtolower($userAgent);

        return match (true) {
            str_contains($ua, 'edg/') => 'Edge',
            str_contains($ua, 'opr/') || str_contains($ua, 'opera') => 'Opera',
            str_contains($ua, 'chrome/') || str_contains($ua, 'chromium/') => 'Chrome',
            str_contains($ua, 'firefox/') => 'Firefox',
            str_contains($ua, 'safari/') => 'Safari',
            default => 'Other',
        };
    }

    private static function trafficSource(string $referrer): string
    {
        if ($referrer === '') {
            return 'direct';
        }

        $host = strtolower((string) parse_url($referrer, PHP_URL_HOST));

        if ($host === '') {
            return 'direct';
        }

        if (str_contains($host, 'google.') || str_contains($host, 'bing.') || str_contains($host, 'yahoo.') || str_contains($host, 'duckduckgo.')) {
            return 'search';
        }

        if (str_contains($host, 'facebook.') || str_contains($host, 'instagram.') || str_contains($host, 'twitter.') || str_contains($host, 'x.com') || str_contains($host, 'youtube.')) {
            return 'social';
        }

        return 'referral';
    }

    private static function countryName(string $code): string
    {
        $countries = [
            'BD' => 'Bangladesh',
            'US' => 'United States',
            'IN' => 'India',
            'PK' => 'Pakistan',
            'GB' => 'United Kingdom',
            'CA' => 'Canada',
            'AU' => 'Australia',
            'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
        ];

        return $countries[$code] ?? $code;
    }

    public static function subjectFromModel(?Model $model): array
    {
        if (!$model) {
            return [
                'subject_type' => null,
                'subject_id' => null,
            ];
        }

        $subjectType = strtolower(class_basename($model));

        return [
            'subject_type' => $subjectType,
            'subject_id' => $model->getKey(),
        ];
    }

    private static function isTableReady(): bool
    {
        if (self::$tableReady !== null) {
            return self::$tableReady;
        }

        self::$tableReady = Schema::hasTable('analytics_events');

        return self::$tableReady;
    }
}
