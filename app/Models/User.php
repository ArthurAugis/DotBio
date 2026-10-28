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
        'avatar',
        'username',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
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
