<?php

declare(strict_types=1);

namespace App\Models;

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

    public function loginMethod(): ?string
    {
        return match (true) {
            $this->discord_id !== null => 'discord',
            $this->google_id !== null => 'google',
            $this->github_id !== null => 'github',
            $this->twitch_id !== null => 'twitch',
            $this->steam_id !== null => 'steam',
            $this->spotify_id !== null => 'spotify',
            $this->patreon_id !== null => 'patreon',
            $this->password !== null => 'password',
            default => null,
        };
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->email_verified_at !== null || $this->loginMethod() !== 'password';
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
}
