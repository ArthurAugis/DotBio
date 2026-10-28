<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

function fakeDiscordUser(string $id): void
{
    $discordUser = (new SocialiteUser)->map([
        'id' => $id,
        'nickname' => 'user'.$id,
        'name' => 'User '.$id,
        'email' => $id.'@example.com',
        'avatar' => 'https://cdn.discordapp.com/avatars/'.$id.'.png',
    ]);

    $provider = Mockery::mock(Provider::class);
    $provider->shouldReceive('user')->andReturn($discordUser);
    Socialite::shouldReceive('driver')->with('discord')->andReturn($provider);
}

test('the first discord user creates the profile', function (): void {
    fakeDiscordUser('111');

    $this->get('/auth/discord/callback')->assertRedirect(route('admin.dashboard'));

    $user = User::where('discord_id', '111')->firstOrFail();
    $this->assertAuthenticatedAs($user);
    expect($user->profile->use_discord_avatar)->toBeTrue();
});

test('an existing discord user can still log in', function (): void {
    $user = User::factory()->create(['discord_id' => '111']);
    Profile::factory()->create(['user_id' => $user->id]);
    fakeDiscordUser('111');

    $this->get('/auth/discord/callback')->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('an unknown discord user is refused once a profile exists', function (): void {
    Profile::factory()->create();
    fakeDiscordUser('222');

    $this->get('/auth/discord/callback')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['discord_id' => '222']);
    expect(Profile::count())->toBe(1);
});
