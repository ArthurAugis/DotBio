<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UpdateDiscordStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expected = config('services.discord.status_secret');

        if (! is_string($expected) || $expected === '') {
            return false;
        }

        $provided = $this->header('X-Bot-Secret');

        return is_string($provided) && hash_equals($expected, $provided);
    }

    public function rules(): array
    {
        return [
            'discord_id' => ['required', 'string'],
            'status' => ['required', 'string', 'in:online,idle,dnd,offline'],
            'activity' => ['nullable', 'string'],
        ];
    }

    protected function failedAuthorization(): never
    {
        throw new HttpException(401, 'Unauthorized: set DISCORD_STATUS_SECRET and send it in the X-Bot-Secret header.');
    }
}
