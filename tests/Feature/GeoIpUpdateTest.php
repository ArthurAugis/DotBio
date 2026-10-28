<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->storage = sys_get_temp_dir().'/dotbio-geoip-'.bin2hex(random_bytes(4));
    File::ensureDirectoryExists($this->storage.'/app/geoip');
    app()->useStoragePath($this->storage);
});

afterEach(function (): void {
    File::deleteDirectory($this->storage);
});

test('the country database is downloaded and installed without any account', function (): void {
    Http::fake(['download.db-ip.com/*' => Http::response(gzencode('fake-mmdb'))]);

    $this->artisan('geoip:update')->assertSuccessful();

    expect(file_get_contents($this->storage.'/app/geoip/country.mmdb'))->toBe('fake-mmdb');
    Http::assertSent(fn ($request) => str_contains($request->url(), 'dbip-country-lite-'.now()->format('Y-m')));
});

test('the previous month is used when the current one is not published yet', function (): void {
    $previous = now()->subMonthNoOverflow()->format('Y-m');
    Http::fake([
        "download.db-ip.com/free/dbip-country-lite-{$previous}.mmdb.gz" => Http::response(gzencode('previous-mmdb')),
        'download.db-ip.com/*' => Http::response('', 404),
    ]);

    $this->artisan('geoip:update')->assertSuccessful();

    expect(file_get_contents($this->storage.'/app/geoip/country.mmdb'))->toBe('previous-mmdb');
});

test('the if-missing option keeps an installed database', function (): void {
    file_put_contents($this->storage.'/app/geoip/country.mmdb', 'existing');
    Http::fake();

    $this->artisan('geoip:update --if-missing')->assertSuccessful();

    Http::assertNothingSent();
});

test('a failed download keeps the current database', function (): void {
    file_put_contents($this->storage.'/app/geoip/country.mmdb', 'existing');
    Http::fake(['download.db-ip.com/*' => Http::response('', 500)]);

    $this->artisan('geoip:update')->assertFailed();

    expect(file_get_contents($this->storage.'/app/geoip/country.mmdb'))->toBe('existing');
});
