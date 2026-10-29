<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = ['email'];

    public static function pendingFor(?string $email): ?self
    {
        return $email === null ? null : static::where('email', Str::lower($email))->first();
    }
}
