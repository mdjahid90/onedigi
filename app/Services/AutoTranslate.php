<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AutoTranslate
{
    public function translate(string $text, string $targetLocale, bool $isHtml = false): string
    {
        $text = (string) $text;
        $targetLocale = strtolower(trim($targetLocale));

        if ($text === '' || $targetLocale === '' || $targetLocale === 'en') {
            return $text;
        }

        $supported = ['bn', 'ru'];
        if (!in_array($targetLocale, $supported, true)) {
            return $text;
        }

        $endpoint = (string) config('services.translate.endpoint');
        $apiKey = (string) (config('services.translate.api_key') ?? '');
        $timeout = (int) (config('services.translate.timeout', 10));

        if ($endpoint === '') {
            return $text;
        }

        $cacheKey = 'auto_translate:' . $targetLocale . ':' . sha1(($isHtml ? '1' : '0') . ':' . $text);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($endpoint, $apiKey, $timeout, $text, $targetLocale, $isHtml) {
            try {
                $payload = [
                    'q' => $text,
                    'source' => 'en',
                    'target' => $targetLocale,
                    'format' => $isHtml ? 'html' : 'text',
                ];

                if ($apiKey !== '') {
                    $payload['api_key'] = $apiKey;
                }

                $res = Http::timeout($timeout)
                    ->asForm()
                    ->post($endpoint, $payload);

                if (!$res->ok()) {
                    return $text;
                }

                $data = $res->json();
                if (is_array($data) && isset($data['translatedText']) && is_string($data['translatedText'])) {
                    return $data['translatedText'];
                }

                return $text;
            } catch (\Throwable $e) {
                return $text;
            }
        });
    }
}
