<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\LinkBlock;
use App\Support\UploadLimit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLinkBlockRequest extends FormRequest
{
    public function rules(): array
    {
        $maxKilobytes = UploadLimit::maxSizeInMegabytes() * 1024;

        return [
            'type' => ['required', Rule::in(LinkBlock::TYPES)],
            'title' => ['nullable', 'required_unless:type,media', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:500', 'regex:~^(https?://|mailto:|/|#)~i'],
            'media_url' => ['nullable', 'string', 'max:500', 'regex:~^(https?://|/)~i'],
            'image_file' => ['nullable', 'image', 'max:'.$maxKilobytes],
            'media_file' => ['nullable', 'file', 'mimetypes:video/mp4,video/webm,video/ogg,video/quicktime', 'max:'.$maxKilobytes],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }
}
