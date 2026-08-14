<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLinkRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'string', 'max:500'],
            'icon' => ['required', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'hover_effect' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Email addresses are accepted without their scheme; normalise them to mailto: links.
     */
    protected function prepareForValidation(): void
    {
        $url = trim((string) $this->input('url'));

        $isEmail = $this->input('icon') === 'envelope'
            || filter_var($url, FILTER_VALIDATE_EMAIL) !== false;

        if ($isEmail && ! str_starts_with($url, 'mailto:')) {
            $this->merge(['url' => 'mailto:'.$url]);
        }
    }
}
