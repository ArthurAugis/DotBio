<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Analytic extends Model
{
    protected $fillable = [
        'profile_id',
        'date',
        'views',
        'clicks',
        'countries',
        'referrers',
        'devices',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'views' => 'integer',
            'clicks' => 'integer',
            'countries' => 'array',
            'referrers' => 'array',
            'devices' => 'array',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
