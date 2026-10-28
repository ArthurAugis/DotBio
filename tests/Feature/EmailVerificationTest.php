<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

function verificationUrl(User $user, ?string $email = null): string
{
    return URL::temporarySignedRoute('verification.verify', now()->addHour(), [
        'id' => $user->id,
        'hash' => sha1($email ?? $user->email),
    ]);
}

test('registering sends a verification email and gates the admin', function (): void {
    Notification::fake();

    $this->post('/register', [
        'username' => 'arthur',
        'email' => 'arthur@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ]);

    $user = User::where('email', 'arthur@example.com')->firstOrFail();

    Notification::assertSentTo($user, VerifyEmail::class);
    expect($user->hasVerifiedEmail())->toBeFalse();
    $this->get('/admin')->assertRedirect(route('verification.notice'));
    $this->get('/email/verify')->assertSuccessful()->assertSee('arthur@example.com');
});

test('the verification link verifies the email', function (): void {
    $user = User::factory()->unverified()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get(verificationUrl($user))->assertRedirect(route('admin.dashboard'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $this->get('/admin')->assertSuccessful();
});

test('a link with the wrong hash is rejected', function (): void {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)->get(verificationUrl($user, 'other@example.com'))->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('the verification link can be resent', function (): void {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->from('/email/verify')
        ->post('/email/verification-notification')
        ->assertRedirect('/email/verify')
        ->assertSessionHas('status');

    Notification::assertSentTo($user, VerifyEmail::class);
});

test('oauth users reach the admin without verifying their email', function (): void {
    $user = User::factory()->unverified()->create(['password' => null, 'github_id' => '333']);
    Profile::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get('/admin')->assertSuccessful();
});

test('existing accounts are marked as verified', function (): void {
    $user = User::factory()->unverified()->create();

    (require database_path('migrations/2026_10_28_000006_mark_existing_users_as_verified.php'))->up();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});
