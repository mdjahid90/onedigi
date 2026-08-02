<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Services\GeoLocationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class BackfillAnalyticsCountries extends Command
{
    protected $signature = 'analytics:backfill-countries {--limit=1000 : Maximum rows to process}';

    protected $description = 'Fill missing analytics country fields from IP or configured local fallback.';

    public function handle(): int
    {
        if (!Schema::hasTable('analytics_events')) {
            $this->warn('analytics_events table does not exist.');

            return self::SUCCESS;
        }

        $limit = max(1, (int) $this->option('limit'));
        $updated = 0;

        AnalyticsEvent::query()
            ->where(function ($query) {
                $query->whereNull('country_code')
                    ->orWhereNull('country_name')
                    ->orWhere('country_name', '')
                    ->orWhere('country_name', 'Unknown');
            })
            ->orderByDesc('id')
            ->limit($limit)
            ->get(['id', 'ip_address', 'country_code', 'country_name'])
            ->each(function (AnalyticsEvent $event) use (&$updated) {
                $countryCode = $event->country_code ? strtoupper((string) $event->country_code) : null;
                $geo = $countryCode
                    ? ['country_code' => $countryCode, 'country_name' => GeoLocationService::countryName($countryCode)]
                    : GeoLocationService::locate($event->ip_address);

                if (!$geo || empty($geo['country_code'])) {
                    return;
                }

                $event->forceFill([
                    'country_code' => strtoupper((string) $geo['country_code']),
                    'country_name' => (string) ($geo['country_name'] ?? GeoLocationService::countryName((string) $geo['country_code'])),
                ])->save();

                $updated++;
            });

        $this->info("Backfilled {$updated} analytics country rows.");

        return self::SUCCESS;
    }
}
