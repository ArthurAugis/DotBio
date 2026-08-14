<?php

declare(strict_types=1);

use App\Models\Analytic;
use App\Models\Profile;
use App\Models\User;

test('the login page renders when no profile exists yet', function (): void {
    $this->get('/login')->assertSuccessful();
});

test('visiting the site without a profile redirects to the login page', function (): void {
    $this->get('/')->assertRedirect('/login');
});

test('the public profile page renders once a profile exists', function (): void {
    Profile::factory()->create(['display_name' => 'Test User']);

    $this->get('/')->assertSuccessful()->assertSee('Test User', false);
});

test('a page view increments the counter and the daily analytics row', function (): void {
    $profile = Profile::factory()->create();

    $this->get('/')->assertSuccessful();

    expect($profile->fresh()->views_count)->toBe(1);

    $analytic = Analytic::where('profile_id', $profile->id)->first();

    expect($analytic)->not->toBeNull()
        ->and($analytic->views)->toBe(1);
});

test('the admin area requires authentication', function (): void {
    $this->get('/admin')->assertRedirect('/login');
});

test('an authenticated user reaches the dashboard', function (): void {
    $this->actingAs(User::factory()->create())->get('/admin')->assertSuccessful();
});

test('an authenticated user reaches the customize page', function (): void {
    $profile = Profile::factory()->create();

    $this->actingAs($profile->user)->get('/admin/customize')->assertSuccessful();
});
