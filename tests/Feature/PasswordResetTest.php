<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

test('the login page links to the forgot password page for a password owner', function (): void {
    Profile::factory()->create();

    $this->get('/login')->assertSee(route('password.request'));
    $this->get('/forgot-password')->assertSuccessful();
});

test('a reset link is sent to an account with a password', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('no reset link is sent to an oauth only account', function (): void {
    Notification::fake();
    $user = User::factory()->create(['password' => null, 'discord_id' => '111']);

    $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
    $this->post('/forgot-password', ['email' => 'nobody@example.com'])->assertSessionHas('status');

    Notification::assertNothingSent();
});

test('the reset link opens the reset page', function (): void {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user): bool {
        $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))
            ->assertSuccessful()
            ->assertSee($user->email);

        return true;
    });
});

test('a valid token resets the password', function (): void {
    $user = User::factory()->create();
    $token = Password::createToken($user);

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertRedirect(route('login'))->assertSessionHas('status');

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});

test('an invalid token does not reset the password', function (): void {
    $user = User::factory()->create();

    $this->from('/reset-password/bad-token')->post('/reset-password', [
        'token' => 'bad-token',
        'email' => $user->email,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertRedirect('/reset-password/bad-token')->assertSessionHasErrors('email');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});
