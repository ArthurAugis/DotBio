<?php

declare(strict_types=1);

use App\Models\Invitation;
use App\Models\Profile;
use App\Models\User;
use App\Notifications\AdminInvitation;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

function discordOwnedPage(): Profile
{
    $owner = User::factory()->create(['password' => null, 'discord_id' => '111', 'avatar' => 'https://avatars.example.com/owner.png']);

    return Profile::factory()->create([
        'user_id' => $owner->id,
        'discord_id' => '111',
        'avatar_url' => 'https://avatars.example.com/owner.png',
        'use_discord_avatar' => true,
    ]);
}

test('the owner invites an email and the invitation is mailed', function (): void {
    Notification::fake();
    $page = discordOwnedPage();

    $this->actingAs($page->user)
        ->post('/admin/team/invitations', ['email' => ' Friend@Example.com '])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('success');

    expect(Invitation::pluck('email')->all())->toBe(['friend@example.com']);
    Notification::assertSentOnDemand(
        AdminInvitation::class,
        fn (AdminInvitation $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'friend@example.com',
    );

    $this->actingAs($page->user)
        ->get('/admin/team')
        ->assertSuccessful()
        ->assertSee('friend@example.com')
        ->assertSee(route('login'));
});

test('an existing admin email cannot be invited twice', function (): void {
    $page = discordOwnedPage();

    $this->actingAs($page->user)
        ->post('/admin/team/invitations', ['email' => $page->user->email])
        ->assertSessionHasErrors('email');

    expect(Invitation::count())->toBe(0);
});

test('an invited email logs in with any provider and edits the page without taking it over', function (string $provider): void {
    $page = discordOwnedPage();
    Invitation::create(['email' => 'friend@example.com']);
    fakeOAuthUser($provider, '222', ['email' => 'Friend@example.com']);

    $this->get("/auth/{$provider}/callback")->assertRedirect(route('admin.dashboard'));

    $admin = User::where($provider.'_id', '222')->firstOrFail();
    $this->assertAuthenticatedAs($admin);
    expect(Invitation::count())->toBe(0)
        ->and($page->fresh()->only(['user_id', 'discord_id', 'avatar_url', 'use_discord_avatar']))->toBe([
            'user_id' => $page->user_id,
            'discord_id' => '111',
            'avatar_url' => 'https://avatars.example.com/owner.png',
            'use_discord_avatar' => true,
        ]);

    $this->post('/admin/seo', [
        'meta_title' => 'Edited by an admin',
        'favicon_type' => 'avatar',
        'meta_robots' => 'index, follow',
    ])->assertRedirect();

    expect($page->fresh()->meta_title)->toBe('Edited by an admin');

    $this->post('/logout');
    fakeOAuthUser($provider, '222', ['email' => 'Friend@example.com']);
    $this->get("/auth/{$provider}/callback")->assertRedirect(route('admin.dashboard'));
    $this->assertAuthenticatedAs($admin);
})->with(['discord', 'patreon', 'google']);

test('an email that was not invited is still refused', function (): void {
    discordOwnedPage();
    Invitation::create(['email' => 'friend@example.com']);
    fakeOAuthUser('patreon', '333', ['email' => 'stranger@example.com']);

    $this->get('/auth/patreon/callback')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['patreon_id' => '333']);
});

test('an invited email can create a password account that needs a verified email', function (): void {
    $page = discordOwnedPage();
    Invitation::create(['email' => 'friend@example.com']);

    $this->get('/login')->assertSee(route('register'));
    $this->get('/register')->assertSuccessful()->assertSee('Create my account');

    $this->post('/register', [
        'username' => 'friend',
        'email' => 'friend@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ])->assertRedirect(route('admin.dashboard'));

    $admin = User::where('email', 'friend@example.com')->firstOrFail();
    $this->assertAuthenticatedAs($admin);
    expect($page->fresh()->user_id)->toBe($page->user_id)
        ->and(Invitation::count())->toBe(0);

    $this->get('/admin')->assertRedirect(route('verification.notice'));
});

test('registering with an email that was not invited is refused', function (): void {
    discordOwnedPage();
    Invitation::create(['email' => 'friend@example.com']);

    $this->post('/register', [
        'username' => 'stranger',
        'email' => 'stranger@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ])->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'stranger@example.com']);
});

test('only the owner manages the team and runs updates', function (): void {
    $page = discordOwnedPage();
    $admin = User::factory()->create(['password' => null, 'google_id' => '222']);

    $this->actingAs($admin)->get('/admin/team')->assertForbidden();
    $this->actingAs($admin)->post('/admin/team/invitations', ['email' => 'x@example.com'])->assertForbidden();
    $this->actingAs($admin)->post("/admin/team/admins/{$admin->id}/transfer")->assertForbidden();
    $this->actingAs($admin)->delete("/admin/team/admins/{$page->user_id}")->assertForbidden();
    $this->actingAs($admin)->get('/admin/update')->assertForbidden();
    $this->actingAs($admin)->get('/admin/links')->assertSuccessful();
});

test('the owner removes an admin but cannot remove themself', function (): void {
    $page = discordOwnedPage();
    $admin = User::factory()->create(['password' => null, 'google_id' => '222']);

    $this->actingAs($page->user)->delete("/admin/team/admins/{$page->user_id}")->assertForbidden();
    $this->actingAs($page->user)->delete("/admin/team/admins/{$admin->id}")->assertRedirect();

    expect(User::find($admin->id))->toBeNull()
        ->and(User::find($page->user_id))->not->toBeNull();
});

test('the owner revokes a pending invitation', function (): void {
    $page = discordOwnedPage();
    $invitation = Invitation::create(['email' => 'friend@example.com']);

    $this->actingAs($page->user)->delete("/admin/team/invitations/{$invitation->id}")->assertRedirect();

    expect(Invitation::count())->toBe(0);
});

test('ownership is transferred to another admin with their avatar and discord', function (): void {
    $page = discordOwnedPage();
    $oldOwner = $page->user;
    $admin = User::factory()->create(['password' => null, 'discord_id' => '222', 'avatar' => 'https://avatars.example.com/new.png']);

    $this->actingAs($oldOwner)
        ->post("/admin/team/admins/{$admin->id}/transfer")
        ->assertRedirect(route('admin.dashboard'));

    expect($page->fresh()->only(['user_id', 'discord_id', 'avatar_url', 'use_discord_avatar']))->toBe([
        'user_id' => $admin->id,
        'discord_id' => '222',
        'avatar_url' => 'https://avatars.example.com/new.png',
        'use_discord_avatar' => true,
    ]);

    $this->actingAs($oldOwner)->get('/admin/team')->assertForbidden();
    $this->actingAs($oldOwner)->get('/admin/links')->assertSuccessful();
    $this->actingAs($admin)->get('/admin/team')->assertSuccessful();

    fakeOAuthUser('discord', '111', ['email' => $oldOwner->email]);
    $this->post('/logout');
    $this->get('/auth/discord/callback')->assertRedirect(route('admin.dashboard'));
    expect($page->fresh()->user_id)->toBe($admin->id);
});

test('ownership can go to an admin without discord', function (): void {
    $page = discordOwnedPage();
    $admin = User::factory()->create(['password' => null, 'patreon_id' => '222', 'avatar' => null]);

    $this->actingAs($page->user)->post("/admin/team/admins/{$admin->id}/transfer");

    expect($page->fresh()->only(['user_id', 'discord_id', 'avatar_url', 'use_discord_avatar']))->toBe([
        'user_id' => $admin->id,
        'discord_id' => null,
        'avatar_url' => 'https://avatars.example.com/owner.png',
        'use_discord_avatar' => false,
    ]);
});
