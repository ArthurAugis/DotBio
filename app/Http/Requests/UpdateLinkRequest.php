<?php

declare(strict_types=1);

namespace App\Http\Requests;

class UpdateLinkRequest extends StoreLinkRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);
    }
}
