<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use PharData;

class InstallGeoLiteDatabase extends Command
{
    protected $signature = 'geoip:install-geolite
        {license_key : MaxMind license key}
        {--edition=GeoLite2-Country : MaxMind edition ID}
        {--path= : Destination .mmdb path}';

    protected $description = 'Download and install a MaxMind GeoLite2 .mmdb database.';

    public function handle(): int
    {
        $licenseKey = trim((string) $this->argument('license_key'));
        $edition = trim((string) $this->option('edition')) ?: 'GeoLite2-Country';
        $destination = (string) ($this->option('path') ?: env('MAXMIND_DB_PATH') ?: storage_path('app/geoip/GeoLite2-Country.mmdb'));

        if ($licenseKey === '') {
            $this->error('License key is required.');

            return self::FAILURE;
        }

        $tmpDir = storage_path('app/geoip/tmp');
        $archivePath = $tmpDir.'/'.$edition.'.tar.gz';

        File::ensureDirectoryExists(dirname($destination));
        File::ensureDirectoryExists($tmpDir);

        $url = 'https://download.maxmind.com/app/geoip_download';

        $this->info('Downloading '.$edition.' from MaxMind...');

        $response = Http::timeout(120)
            ->sink($archivePath)
            ->get($url, [
                'edition_id' => $edition,
                'license_key' => $licenseKey,
                'suffix' => 'tar.gz',
            ]);

        if (!$response->successful()) {
            @unlink($archivePath);
            $this->error('Download failed. HTTP '.$response->status().'. Check your MaxMind license key and GeoLite access.');

            return self::FAILURE;
        }

        try {
            $tarPath = substr($archivePath, 0, -3);
            @unlink($tarPath);

            $gz = new PharData($archivePath);
            $gz->decompress();

            $tar = new PharData($tarPath);
            $tar->extractTo($tmpDir, null, true);
        } catch (\Throwable $exception) {
            $this->error('Could not extract database archive: '.$exception->getMessage());

            return self::FAILURE;
        }

        $matches = File::glob($tmpDir.'/**/'.$edition.'.mmdb');
        if (!$matches) {
            $this->error('Downloaded archive did not contain '.$edition.'.mmdb.');

            return self::FAILURE;
        }

        File::copy($matches[0], $destination);

        $this->info('Installed GeoLite database: '.$destination);

        return self::SUCCESS;
    }
}
