<?php

namespace App\Services;

use GeoIp2\Database\Reader;

class GeoLocationService
{
    private static bool $readerChecked = false;

    private static ?Reader $reader = null;

    public static function locate(?string $ip): ?array
    {
        if (!$ip) {
            return null;
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return self::localFallback();
        }

        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        $reader = self::reader();
        if (!$reader) {
            return null;
        }

        try {
            $record = self::readCity($reader, $ip) ?: self::readCountry($reader, $ip);
        } catch (\Throwable $exception) {
            report($exception);
            return null;
        }

        if (!$record?->country?->isoCode) {
            return null;
        }

        return [
            'country_code' => strtoupper((string) $record->country->isoCode),
            'country_name' => (string) ($record->country->name ?: $record->country->isoCode),
        ];
    }

    private static function reader(): ?Reader
    {
        if (self::$readerChecked) {
            return self::$reader;
        }

        self::$readerChecked = true;

        foreach (self::databaseCandidates() as $path) {
            if ($path && is_file($path)) {
                try {
                    self::$reader = new Reader($path);
                    return self::$reader;
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }

        return null;
    }

    private static function databaseCandidates(): array
    {
        return array_values(array_unique(array_filter([
            env('MAXMIND_DB_PATH'),
            env('GEOIP_DATABASE_PATH'),
            storage_path('app/geoip/GeoLite2-City.mmdb'),
            storage_path('app/geoip/GeoLite2-Country.mmdb'),
            database_path('GeoLite2-City.mmdb'),
            database_path('GeoLite2-Country.mmdb'),
        ])));
    }

    private static function localFallback(): ?array
    {
        $code = strtoupper((string) env('ANALYTICS_LOCAL_COUNTRY_CODE', app()->environment(['local', 'testing']) ? 'BD' : ''));
        $code = strlen($code) === 2 ? $code : null;

        if (!$code) {
            return null;
        }

        return [
            'country_code' => $code,
            'country_name' => self::countryName($code),
        ];
    }

    public static function countryName(string $code): string
    {
        $code = strtoupper($code);

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
            'QA' => 'Qatar',
            'KW' => 'Kuwait',
            'OM' => 'Oman',
            'BH' => 'Bahrain',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
            'ID' => 'Indonesia',
            'PH' => 'Philippines',
            'TH' => 'Thailand',
            'VN' => 'Vietnam',
            'CN' => 'China',
            'JP' => 'Japan',
            'KR' => 'South Korea',
            'RU' => 'Russia',
            'DE' => 'Germany',
            'FR' => 'France',
            'IT' => 'Italy',
            'ES' => 'Spain',
            'NL' => 'Netherlands',
            'SE' => 'Sweden',
            'NO' => 'Norway',
            'BR' => 'Brazil',
            'MX' => 'Mexico',
            'ZA' => 'South Africa',
        ];

        return $countries[$code] ?? $code;
    }

    private static function readCity(Reader $reader, string $ip): mixed
    {
        try {
            return $reader->city($ip);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function readCountry(Reader $reader, string $ip): mixed
    {
        try {
            return $reader->country($ip);
        } catch (\Throwable) {
            return null;
        }
    }
}
