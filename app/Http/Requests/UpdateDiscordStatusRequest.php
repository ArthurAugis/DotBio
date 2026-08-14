<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UpdateDiscordStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expected = config('services.discord.status_secret')
            ?? config('services.discord.bot_token');

        if (! $expected) {
            return false;
        }

        $provided = $this->header('X-Bot-Secret') ?? $this->input('bot_secret');

        return is_string($provided) && hash_equals((string) $expected, $provided);
    }

    public function rules(): array
    {
        return [
            'discord_id' => ['required', 'string'],
            'status' => ['required', 'string', 'in:online,idle,dnd,offline'],
            'activity' => ['nullable', 'string'],
            'bot_secret' => ['nullable', 'string'],
        ];
    }

    protected function failedAuthorization(): never
    {
        throw new HttpException(401, 'Unauthorized: invalid or missing bot secret key.');
    }
}
