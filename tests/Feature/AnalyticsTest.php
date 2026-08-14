<?php

declare(strict_types=1);

use App\Models\Analytic;
use App\Models\Profile;

test('the analytics page lists country names resolved from stored country codes', function (): void {
    $profile = Profile::factory()->create();

    Analytic::create([
        'profile_id' => $profile->id,
        'date' => now()->toDateString(),
        'views' => 7,
        'clicks' => 2,
        'countries' => ['HU' => 5, 'FR' => 2],
    ]);

    $this->actingAs($profile->user)
        ->get('/admin/analytics')
        ->assertSuccessful()
        ->assertSee('Hungary')
        ->assertSee('France');
});

test('unknown or malformed country codes are ignored', function (): void {
    $profile = Profile::factory()->create();

    Analytic::create([
        'profile_id' => $profile->id,
        'date' => now()->toDateString(),
        'views' => 3,
        'countries' => ['ZZZ' => 1, 'fr' => 2],
    ]);

    $this->actingAs($profile->user)
        ->get('/admin/analytics')
        ->assertSuccessful()
        ->assertSee('France');
});
