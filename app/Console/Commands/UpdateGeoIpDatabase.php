<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class UpdateGeoIpDatabase extends Command
{
    protected $signature = 'geoip:update {--if-missing : Only download when the database is not installed yet}';

    protected $description = 'Download the free DB-IP country database used for analytics countries';

    private const DOWNLOAD_URL = 'https://download.db-ip.com/free/dbip-country-lite-%s.mmdb.gz';

    public function handle(): int
    {
        $target = storage_path('app/geoip/country.mmdb');

        if ($this->option('if-missing') && is_file($target)) {
            return self::SUCCESS;
        }

        File::ensureDirectoryExists(dirname($target));

        try {
            $compressed = $this->download();
            $database = $compressed === null ? false : gzdecode($compressed);

            if ($database === false || $database === '') {
                $this->error('Could not download the DB-IP country database.');

                return self::FAILURE;
            }

            $temporary = $target.'.tmp';
            file_put_contents($temporary, $database);
            rename($temporary, $target);
        } catch (Throwable $exception) {
            $this->error('GeoIP download failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('DB-IP country database installed.');

        return self::SUCCESS;
    }

    private function download(): ?string
    {
        foreach ([now(), now()->subMonthNoOverflow()] as $month) {
            $response = Http::timeout(120)->get(sprintf(self::DOWNLOAD_URL, $month->format('Y-m')));

            if ($response->successful()) {
                return $response->body();
            }
        }

        return null;
    }
}
