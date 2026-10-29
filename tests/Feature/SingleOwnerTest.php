<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\Profile;
use App\Models\User;

test('the login page offers every method but no sign up once the page is claimed', function (): void {
    Profile::factory()->for(User::factory()->state(['password' => null, 'discord_id' => '111']))->create();

    $this->get('/login')
        ->assertSuccessful()
        ->assertSee(route('auth.redirect', 'discord'))
        ->assertSee(route('auth.redirect', 'google'))
        ->assertSee(route('login.store'))
        ->assertDontSee(route('register'));
});

test('a wrong password is refused', function (): void {
    $owner = User::factory()->create();
    Profile::factory()->create(['user_id' => $owner->id]);

    $this->post('/login', ['email' => $owner->email, 'password' => 'wrong-password'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('an unclaimed seeded page can still be claimed by registering', function (): void {
    $page = Profile::factory()->for(User::factory()->state(['password' => null]))->create();

    $this->post('/register', [
        'username' => 'arthur',
        'email' => 'arthur@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ])->assertRedirect(route('admin.dashboard'));

    expect(Profile::count())->toBe(1)
        ->and($page->fresh()->user->email)->toBe('arthur@example.com');
});

test('the cleanup migration keeps only the first account and the displayed page', function (): void {
    $seeded = User::factory()->create(['password' => null]);
    $page = Profile::factory()->create(['user_id' => $seeded->id]);

    $owner = User::factory()->create(['password' => null, 'discord_id' => '111']);
    $ownerProfile = Profile::factory()->create(['user_id' => $owner->id, 'discord_id' => '111']);

    $stranger = User::factory()->create(['password' => null, 'discord_id' => '222']);
    $strangerProfile = Profile::factory()->create(['user_id' => $stranger->id]);
    Link::create([
        'profile_id' => $strangerProfile->id,
        'title' => 'Spam',
        'url' => 'https://example.com',
        'icon' => 'globe',
        'sort_order' => 1,
    ]);

    (require database_path('migrations/2026_10_28_000002_keep_only_the_first_account.php'))->up();

    expect(User::pluck('id')->all())->toBe([$owner->id])
        ->and(Profile::pluck('id')->all())->toBe([$page->id])
        ->and($page->fresh()->user_id)->toBe($owner->id)
        ->and($page->fresh()->discord_id)->toBe('111')
        ->and(Link::count())->toBe(0)
        ->and(Profile::find($ownerProfile->id))->toBeNull();
});
