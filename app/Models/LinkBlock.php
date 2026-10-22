<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkBlock extends Model
{
    public const TYPES = ['card', 'button', 'media', 'heading'];

    protected $fillable = [
        'profile_id',
        'type',
        'title',
        'subtitle',
        'url',
        'image_url',
        'media_url',
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

    /**
     * @return array{kind: 'iframe'|'video', src: string, height: int|null}|null
     */
    public function getEmbedAttribute(): ?array
    {
        $url = trim((string) $this->media_url);

        if ($url === '') {
            return null;
        }

        if (preg_match('~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([\w-]{11})~', $url, $match)) {
            return ['kind' => 'iframe', 'src' => 'https://www.youtube-nocookie.com/embed/'.$match[1], 'height' => null];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $match)) {
            return ['kind' => 'iframe', 'src' => 'https://player.vimeo.com/video/'.$match[1], 'height' => null];
        }

        if (preg_match('~open\.spotify\.com/(?:intl-[\w-]+/)?(track|album|playlist|episode|show|artist)/(\w+)~', $url, $match)) {
            $compact = in_array($match[1], ['track', 'episode'], true);

            return ['kind' => 'iframe', 'src' => "https://open.spotify.com/embed/{$match[1]}/{$match[2]}", 'height' => $compact ? 152 : 352];
        }

        if (preg_match('~^https://(?:www\.|m\.)?soundcloud\.com/~', $url)) {
            return ['kind' => 'iframe', 'src' => 'https://w.soundcloud.com/player/?url='.urlencode($url), 'height' => 166];
        }

        if (preg_match('~\.(mp4|webm|ogg|mov)(\?.*)?$~i', $url)) {
            return ['kind' => 'video', 'src' => $url, 'height' => null];
        }

        return null;
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }
}
