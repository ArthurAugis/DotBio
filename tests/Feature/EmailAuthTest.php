<?php

declare(strict_types=1);

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function registrationData(array $overrides = []): array
{
    return array_merge([
        'username' => 'arthur',
        'email' => 'arthur@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
    ], $overrides);
}

test('the register page is shown when no profile exists', function (): void {
    $this->get('/register')->assertSuccessful();
});

test('a user registers with email, username and avatar', function (): void {
    Storage::fake('public');

    $this->post('/register', registrationData([
        'avatar_file' => UploadedFile::fake()->image('me.png'),
    ]))->assertRedirect(route('admin.dashboard'));

    $user = User::where('email', 'arthur@example.com')->firstOrFail();
    $profile = $user->profile;

    $this->assertAuthenticatedAs($user);
    expect($user->username)->toBe('arthur')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue()
        ->and($profile->display_name)->toBe('arthur')
        ->and($profile->use_discord_avatar)->toBeFalse()
        ->and($profile->avatar_url)->toStartWith('/storage/uploads/avatars/');

    Storage::disk('public')->assertExists(str_replace('/storage/', '', $profile->avatar_url));

    $this->get('/admin')->assertSuccessful();
});

test('the username must be unique', function (): void {
    User::factory()->create(['username' => 'arthur']);

    $this->from('/register')
        ->post('/register', registrationData())
        ->assertRedirect('/register')
        ->assertSessionHasErrors('username');

    $this->assertGuest();
});

test('registration is closed once a profile exists', function (): void {
    Profile::factory()->create();

    $this->get('/register')->assertRedirect(route('login'));
    $this->post('/register', registrationData())->assertForbidden();
    $this->assertDatabaseMissing('users', ['email' => 'arthur@example.com']);
});

test('a user logs in with email and password', function (): void {
    $profile = Profile::factory()->create();

    $this->post('/login', [
        'email' => $profile->user->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($profile->user);
});

test('a wrong password is rejected', function (): void {
    $profile = Profile::factory()->create();

    $this->from('/login')->post('/login', [
        'email' => $profile->user->email,
        'password' => 'wrong-password',
    ])->assertRedirect('/login')->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('a discord user without password cannot log in by email', function (): void {
    $user = User::factory()->create(['password' => null, 'discord_id' => '123']);

    $this->post('/login', ['email' => $user->email, 'password' => ''])
        ->assertSessionHasErrors('password');
    $this->post('/login', ['email' => $user->email, 'password' => 'anything'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('the login page offers both email and discord sign in', function (): void {
    $this->get('/login')
        ->assertSuccessful()
        ->assertSee(route('login.store'))
        ->assertSee(route('auth.redirect', 'discord'));
});
