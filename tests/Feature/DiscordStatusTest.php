<?php

declare(strict_types=1);

use App\Models\Profile;

test('the status endpoint returns the stored status and activity', function (): void {
    Profile::factory()->create([
        'discord_id' => '123456789012345678',
        'custom_discord_status' => 'dnd',
        'custom_status_text' => 'Coding on DotBio',
    ]);

    $this->get('/api/discord-status/123456789012345678')
        ->assertSuccessful()
        ->assertJson([
            'status' => 'dnd',
            'activity' => 'Coding on DotBio',
        ]);
});

test('the status endpoint reports offline for an unknown discord id', function (): void {
    $this->get('/api/discord-status/000000000000000000')
        ->assertSuccessful()
        ->assertJson(['status' => 'offline', 'activity' => '']);
});

test('a status push without the bot secret is rejected', function (): void {
    config(['services.discord.status_secret' => 'test_secret_key_123']);

    Profile::factory()->create(['discord_id' => '987654321098765432']);

    $this->postJson('/api/discord/update-status', [
        'discord_id' => '987654321098765432',
        'status' => 'online',
    ])->assertStatus(401);
});

test('a status push with a wrong bot secret is rejected', function (): void {
    config(['services.discord.status_secret' => 'test_secret_key_123']);

    Profile::factory()->create(['discord_id' => '987654321098765432']);

    $this->postJson('/api/discord/update-status', [
        'discord_id' => '987654321098765432',
        'status' => 'online',
        'bot_secret' => 'wrong',
    ])->assertStatus(401);
});

test('a status push with the bot secret updates the profile', function (): void {
    config(['services.discord.status_secret' => 'test_secret_key_123']);

    $profile = Profile::factory()->create([
        'discord_id' => '987654321098765432',
        'custom_discord_status' => 'offline',
    ]);

    $this->postJson('/api/discord/update-status', [
        'discord_id' => '987654321098765432',
        'status' => 'online',
        'activity' => 'Playing Valorant',
        'bot_secret' => 'test_secret_key_123',
    ])->assertSuccessful()->assertJson([
        'success' => true,
        'status' => 'online',
        'activity' => 'Playing Valorant',
    ]);

    expect($profile->fresh())
        ->custom_discord_status->toBe('online')
        ->custom_status_text->toBe('Playing Valorant');
});

test('going offline stamps the last seen timestamp', function (): void {
    config(['services.discord.status_secret' => 'secret']);

    $profile = Profile::factory()->create([
        'discord_id' => '111111111111111111',
        'custom_discord_status' => 'online',
    ]);

    $this->postJson('/api/discord/update-status', [
        'discord_id' => '111111111111111111',
        'status' => 'offline',
        'bot_secret' => 'secret',
    ])->assertSuccessful();

    expect($profile->fresh()->last_seen_at)->not->toBeNull();
});

test('a status push for an unknown discord id returns not found', function (): void {
    config(['services.discord.status_secret' => 'secret']);

    $this->postJson('/api/discord/update-status', [
        'discord_id' => '000000000000000000',
        'status' => 'online',
        'bot_secret' => 'secret',
    ])->assertStatus(404);
});
