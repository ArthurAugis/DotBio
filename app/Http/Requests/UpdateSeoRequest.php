<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSeoRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'meta_keywords' => ['nullable', 'string', 'max:550'],
            'meta_robots' => ['required', 'string', Rule::in(['index, follow', 'noindex, nofollow'])],
            'favicon_type' => ['required', 'string', 'in:avatar,custom'],
            'favicon_file' => ['nullable', 'file', 'mimes:ico,png,jpg,jpeg,svg,webp', 'max:4096'],
            'meta_image_file' => ['nullable', 'file', 'mimes:png,jpg,jpeg,webp', 'max:5120'],
        ];
    }
}
