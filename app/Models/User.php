<?php

declare(strict_types=1);

namespace App\Models;

use App\Http\Controllers\Auth\OAuthController;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'discord_id',
        'google_id',
        'github_id',
        'twitch_id',
        'steam_id',
        'spotify_id',
        'patreon_id',
        'avatar',
        'username',
        'password',
        'oauth_tokens',
        'last_login_provider',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'oauth_tokens',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'oauth_tokens' => 'encrypted:array',
        ];
    }

    /**
     * @return list<string>
     */
    public function linkedProviders(): array
    {
        return array_values(array_filter(
            OAuthController::PROVIDERS,
            fn (string $provider): bool => $this->{$provider.'_id'} !== null,
        ));
    }

    public function loginMethod(): ?string
    {
        $methods = [...$this->linkedProviders(), ...($this->password !== null ? ['password'] : [])];

        return in_array($this->last_login_provider, $methods, true)
            ? $this->last_login_provider
            : ($methods[0] ?? null);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null || $this->password === null || $this->linkedProviders() !== [];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
}
