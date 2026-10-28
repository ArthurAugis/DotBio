<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeOAuthUser(string $provider, string $id, array $attributes = []): void
{
    $account = (new SocialiteUser)->map($attributes + [
        'id' => $id,
        'nickname' => 'user'.$id,
        'name' => 'User '.$id,
        'email' => $id.'@example.com',
        'avatar' => 'https://avatars.example.com/'.$id.'.png',
    ]);

    $driver = Mockery::mock(Provider::class);
    $driver->shouldReceive('user')->andReturn($account);
    Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
}

test('the first discord user creates the profile', function (): void {
    fakeOAuthUser('discord', '111');

    $this->get('/auth/discord/callback')->assertRedirect(route('admin.dashboard'));

    $user = User::where('discord_id', '111')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->profile->use_discord_avatar)->toBeTrue()
        ->and($user->profile->discord_id)->toBe('111');
});

test('an existing discord owner can still log in', function (): void {
    $user = User::factory()->create(['password' => null, 'discord_id' => '111']);
    Profile::factory()->create(['user_id' => $user->id]);
    fakeOAuthUser('discord', '111');

    $this->get('/auth/discord/callback')->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('an unknown discord user is refused once the page has an owner', function (): void {
    Profile::factory()->create();
    fakeOAuthUser('discord', '222');

    $this->get('/auth/discord/callback')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['discord_id' => '222']);
});

test('the first user creates the profile with the provider avatar', function (string $provider): void {
    fakeOAuthUser($provider, '333', ['nickname' => 'yoshi']);

    $this->get("/auth/{$provider}/callback")->assertRedirect(route('admin.dashboard'));

    $user = User::where($provider.'_id', '333')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->username)->toBe('yoshi')
        ->and($user->loginMethod())->toBe($provider)
        ->and($user->profile->display_name)->toBe('yoshi')
        ->and($user->profile->avatar_url)->toBe('https://avatars.example.com/333.png')
        ->and($user->profile->use_discord_avatar)->toBeFalse();

    $this->post('/logout');
    $this->get('/login')
        ->assertSee(route('auth.redirect', $provider))
        ->assertDontSee(route('auth.redirect', 'discord'))
        ->assertDontSee(route('login.store'));
})->with(['google']);

test('another provider is refused when the owner signed up with google', function (): void {
    $user = User::factory()->create(['password' => null, 'google_id' => '333']);
    Profile::factory()->create(['user_id' => $user->id]);
    fakeOAuthUser('discord', '333');

    $this->get('/auth/discord/callback')->assertRedirect(route('login'));

    $this->assertGuest();
});

test('an existing account with the same email is linked instead of duplicated', function (): void {
    $seeded = User::factory()->create(['password' => null, 'email' => 'yoshi@example.com']);
    $page = Profile::factory()->create(['user_id' => $seeded->id]);
    fakeOAuthUser('google', '444', ['email' => 'yoshi@example.com']);

    $this->get('/auth/google/callback')->assertRedirect(route('admin.dashboard'));

    expect(User::count())->toBe(1)
        ->and($seeded->fresh()->google_id)->toBe('444')
        ->and($page->fresh()->user_id)->toBe($seeded->id);
});

test('a taken username does not block the sign in', function (): void {
    $seeded = User::factory()->create(['password' => null, 'username' => 'yoshi']);
    Profile::factory()->create(['user_id' => $seeded->id]);
    fakeOAuthUser('google', '555', ['nickname' => 'yoshi']);

    $this->get('/auth/google/callback')->assertRedirect(route('admin.dashboard'));

    $user = User::where('google_id', '555')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->username)->toBeNull()
        ->and($user->profile->display_name)->not->toBeNull();
});

test('unknown providers are not routed', function (): void {
    $this->get('/auth/github')->assertNotFound();
});
