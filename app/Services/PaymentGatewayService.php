<?php

namespace App\Services;

use App\Models\PaymentGateway;
use Illuminate\Support\Collection;

class PaymentGatewayService
{
    public const UDDOKTAPAY = 'uddoktapay';
    public const PIPRAPAY = 'piprapay';

    public static function supported(): array
    {
        return [
            self::UDDOKTAPAY => [
                'name' => 'UddoktaPay',
                'description' => 'BDT checkout through your configured UddoktaPay API.',
                'requires' => ['base_url', 'api_key'],
                'sort_order' => 10,
            ],
            self::PIPRAPAY => [
                'name' => 'PipraPay',
                'description' => 'Hosted checkout through your configured PipraPay API.',
                'requires' => ['base_url', 'api_key'],
                'sort_order' => 20,
            ],
        ];
    }

    public static function syncSupportedGateways(): void
    {
        foreach (self::supported() as $code => $meta) {
            $gateway = PaymentGateway::query()->firstOrNew(['code' => $code]);
            $gateway->fill([
                'name' => $gateway->exists ? $gateway->name : $meta['name'],
                'base_url' => $gateway->base_url ?: (self::envBaseUrl($code) ?: null),
                'api_key' => $gateway->api_key ?: (self::envApiKey($code) ?: null),
                'mode' => $gateway->mode ?: 'TEST',
                'sort_order' => (int) $meta['sort_order'],
            ]);

            if (!$gateway->exists) {
                $gateway->is_active = self::envBaseUrl($code) !== '' && self::envApiKey($code) !== '';
            }

            $gateway->save();
        }
    }

    /**
     * @return Collection<int, PaymentGateway>
     */
    public static function supportedQuery(): Collection
    {
        self::syncSupportedGateways();

        return PaymentGateway::query()
            ->whereIn('code', array_keys(self::supported()))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, PaymentGateway>
     */
    public static function activeGateways(): Collection
    {
        return self::supportedQuery()
            ->filter(fn (PaymentGateway $gateway): bool => $gateway->is_active && self::isConfigured($gateway))
            ->values();
    }

    public static function activeByCode(?string $code): ?PaymentGateway
    {
        $code = strtolower(trim((string) $code));
        if ($code === '') {
            return self::activeGateways()->first();
        }

        return self::activeGateways()->firstWhere('code', $code);
    }

    public static function gatewayByCode(string $code): ?PaymentGateway
    {
        self::syncSupportedGateways();

        return PaymentGateway::query()
            ->where('code', strtolower(trim($code)))
            ->first();
    }

    public static function isConfigured(PaymentGateway $gateway): bool
    {
        return match ($gateway->code) {
            self::UDDOKTAPAY => trim((string) $gateway->base_url) !== '' && trim((string) $gateway->api_key) !== '',
            self::PIPRAPAY => trim((string) $gateway->base_url) !== '' && trim((string) $gateway->api_key) !== '',
            default => false,
        };
    }

    public static function label(PaymentGateway $gateway): string
    {
        return (string) (self::supported()[$gateway->code]['name'] ?? $gateway->name);
    }

    private static function envBaseUrl(string $code): string
    {
        return match ($code) {
            self::UDDOKTAPAY => (string) config('services.uddoktapay.base_url'),
            self::PIPRAPAY => (string) config('services.piprapay.base_url'),
            default => '',
        };
    }

    private static function envApiKey(string $code): string
    {
        return match ($code) {
            self::UDDOKTAPAY => (string) config('services.uddoktapay.api_key'),
            self::PIPRAPAY => (string) config('services.piprapay.api_key'),
            default => '',
        };
    }
}
