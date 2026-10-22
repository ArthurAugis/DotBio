<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkPageSettingsRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'link_page_enabled' => ['required', 'boolean'],
            'link_page_direction' => ['required', 'in:right,left,top,bottom'],
            'link_page_button_text' => ['nullable', 'string', 'max:60'],
            'link_page_title' => ['nullable', 'string', 'max:120'],
        ];
    }
}
