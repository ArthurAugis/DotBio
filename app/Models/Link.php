<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    use HasFactory;

    private const SOLID_ICONS = ['envelope', 'globe'];

    protected $fillable = [
        'profile_id',
        'title',
        'url',
        'icon',
        'color',
        'hover_effect',
        'sort_order',
        'is_visible',
        'clicks_count',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
            'clicks_count' => 'integer',
        ];
    }

    public function getIconClassAttribute(): string
    {
        $icon = (string) $this->icon;

        if ($icon === '') {
            return 'fa-solid fa-globe';
        }

        if (str_contains($icon, ' ')) {
            return $icon;
        }

        return in_array($icon, self::SOLID_ICONS, true)
            ? 'fa-solid fa-'.$icon
            : 'fa-brands fa-'.$icon;
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
