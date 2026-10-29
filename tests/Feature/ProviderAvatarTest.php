<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    config([
        'services.twitch.client_id' => 'twitch-id',
        'services.steam.client_secret' => 'steam-key',
    ]);
});

function ownerWith(string $provider, array $userAttributes = [], ?string $pageAvatar = 'https://old.example.com/a.png'): Profile
{
    $owner = User::factory()->create([
        'password' => null,
        $provider.'_id' => '42',
        'avatar' => 'https://old.example.com/a.png',
    ] + $userAttributes);

    return Profile::factory()->create(['user_id' => $owner->id, 'avatar_url' => $pageAvatar]);
}

test('the scheduled refresh pulls the new avatar from each provider', function (string $provider, array $fakes, array $userAttributes): void {
    Http::fake(array_map(fn (array $body) => Http::response($body), $fakes));
    $page = ownerWith($provider, $userAttributes);

    $this->artisan('avatars:refresh')->assertSuccessful();

    expect($page->user->fresh()->avatar)->toBe('https://new.example.com/a.png')
        ->and($page->fresh()->avatar_url)->toBe('https://new.example.com/a.png');
})->with([
    'github' => ['github', [
        'api.github.com/user/42' => ['avatar_url' => 'https://new.example.com/a.png'],
    ], []],
    'steam' => ['steam', [
        'api.steampowered.com/*' => ['response' => ['players' => [['avatarmedium' => 'https://new.example.com/a.png']]]],
    ], []],
    'twitch' => ['twitch', [
        'id.twitch.tv/oauth2/token' => ['access_token' => 'app-token'],
        'api.twitch.tv/helix/users*' => ['data' => [['profile_image_url' => 'https://new.example.com/a.png']]],
    ], []],
    'spotify' => ['spotify', [
        'accounts.spotify.com/api/token' => ['access_token' => 'app-token'],
        'api.spotify.com/v1/users/42' => ['images' => [['url' => 'https://new.example.com/a.png']]],
    ], []],
    'google' => ['google', [
        'oauth2.googleapis.com/token' => ['access_token' => 'user-token'],
        'www.googleapis.com/oauth2/v3/userinfo' => ['picture' => 'https://new.example.com/a.png'],
    ], ['oauth_tokens' => ['google' => 'refresh-1']]],
    'patreon' => ['patreon', [
        'www.patreon.com/api/oauth2/token' => ['access_token' => 'user-token', 'refresh_token' => 'refresh-2'],
        'www.patreon.com/api/oauth2/v2/identity*' => ['data' => ['attributes' => ['image_url' => 'https://new.example.com/a.png']]],
    ], ['oauth_tokens' => ['patreon' => 'refresh-1']]],
]);

test('the patreon refresh token is rotated and stored encrypted', function (): void {
    Http::fake([
        'www.patreon.com/api/oauth2/token' => Http::response(['access_token' => 'user-token', 'refresh_token' => 'refresh-2']),
        'www.patreon.com/api/oauth2/v2/identity*' => Http::response(['data' => ['attributes' => ['image_url' => 'https://new.example.com/a.png']]]),
    ]);
    $page = ownerWith('patreon', ['oauth_tokens' => ['patreon' => 'refresh-1']]);

    $this->artisan('avatars:refresh')->assertSuccessful();

    Http::assertSent(fn (Request $request): bool => ($request->data()['refresh_token'] ?? null) === 'refresh-1');
    expect($page->user->fresh()->oauth_tokens)->toBe(['patreon' => 'refresh-2'])
        ->and(DB::table('users')->value('oauth_tokens'))->not->toContain('refresh-2');
});

test('a custom page avatar is kept while the account avatar still refreshes', function (): void {
    Http::fake(['api.github.com/*' => Http::response(['avatar_url' => 'https://new.example.com/a.png'])]);
    $page = ownerWith('github', [], '/uploads/avatars/custom.png');

    $this->artisan('avatars:refresh')->assertSuccessful();

    expect($page->fresh()->avatar_url)->toBe('/uploads/avatars/custom.png')
        ->and($page->user->fresh()->avatar)->toBe('https://new.example.com/a.png');
});

test('an admin avatar refresh never touches the page', function (): void {
    Http::fake(['api.github.com/*' => Http::response(['avatar_url' => 'https://new.example.com/a.png'])]);
    $page = ownerWith('discord');
    $admin = User::factory()->create(['password' => null, 'github_id' => '7', 'avatar' => 'https://old.example.com/a.png']);

    $this->artisan('avatars:refresh')->assertSuccessful();

    expect($admin->fresh()->avatar)->toBe('https://new.example.com/a.png')
        ->and($page->fresh()->avatar_url)->toBe('https://old.example.com/a.png');
});

test('discord, password and token-less accounts are skipped', function (): void {
    Http::fake();
    ownerWith('discord');
    User::factory()->create();
    User::factory()->create(['password' => null, 'google_id' => '9']);

    $this->artisan('avatars:refresh')->assertSuccessful();

    Http::assertNothingSent();
});

test('a failing provider does not stop the other accounts', function (): void {
    Http::fake([
        'api.steampowered.com/*' => Http::response([], 500),
        'api.github.com/*' => Http::response(['avatar_url' => 'https://new.example.com/a.png']),
    ]);
    User::factory()->create(['password' => null, 'steam_id' => '1', 'avatar' => 'https://old.example.com/s.png']);
    $github = User::factory()->create(['password' => null, 'github_id' => '2']);

    $this->artisan('avatars:refresh')->assertSuccessful();

    expect($github->fresh()->avatar)->toBe('https://new.example.com/a.png');
});

test('logging in keeps the google refresh token but not other providers tokens', function (string $provider, bool $stored): void {
    fakeOAuthUser($provider, '555', ['refreshToken' => 'refresh-1']);

    $this->get("/auth/{$provider}/callback")->assertRedirect(route('admin.dashboard'));

    expect(User::where($provider.'_id', '555')->firstOrFail()->oauth_tokens)
        ->toBe($stored ? [$provider => 'refresh-1'] : null);
})->with([
    ['google', true],
    ['patreon', true],
    ['github', false],
]);

test('the owner login replaces a stale provider avatar on the page', function (): void {
    $page = ownerWith('patreon');
    fakeOAuthUser('patreon', '42', ['email' => $page->user->email, 'avatar' => 'https://new.example.com/a.png']);

    $this->get('/auth/patreon/callback')->assertRedirect(route('admin.dashboard'));

    expect($page->fresh()->avatar_url)->toBe('https://new.example.com/a.png');
});
