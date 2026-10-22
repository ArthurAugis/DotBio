<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Profile;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLinkPageSettingsRequest extends FormRequest
{
    private const STYLE_RULES = [
        'bg_color' => ['regex:/^#[0-9a-f]{6}$/i'],
        'bg_opacity' => ['integer', 'min:0', 'max:100'],
        'blur' => ['integer', 'min:0', 'max:40'],
        'border_color' => ['regex:/^#[0-9a-f]{6}$/i'],
        'border_opacity' => ['integer', 'min:0', 'max:100'],
        'border_width' => ['integer', 'min:0', 'max:10'],
        'radius' => ['integer', 'min:0', 'max:60'],
        'text_color' => ['regex:/^#[0-9a-f]{6}$/i'],
        'subtitle_color' => ['regex:/^#[0-9a-f]{6}$/i'],
        'hover_effect' => ['in:none,lift,scale,glow'],
        'hover_bg_color' => ['regex:/^#[0-9a-f]{6}$/i'],
        'hover_bg_opacity' => ['integer', 'min:0', 'max:100'],
        'hover_border_color' => ['regex:/^#[0-9a-f]{6}$/i'],
        'hover_text_color' => ['regex:/^#[0-9a-f]{6}$/i'],
        'glow_color' => ['regex:/^#[0-9a-f]{6}$/i'],
    ];

    public function rules(): array
    {
        $rules = [
            'link_page_enabled' => ['required', 'boolean'],
            'link_page_direction' => ['required', 'in:right,left,top,bottom'],
            'link_page_button_text' => ['nullable', 'string', 'max:60'],
            'link_page_title' => ['nullable', 'string', 'max:120'],
            'link_page_style' => ['sometimes', 'array:'.implode(',', array_keys(Profile::LINK_PAGE_STYLE_DEFAULTS))],
        ];

        foreach (array_keys(Profile::LINK_PAGE_STYLE_DEFAULTS) as $group) {
            foreach (self::STYLE_RULES as $field => $fieldRules) {
                $rules["link_page_style.{$group}.{$field}"] = ['required_with:link_page_style', ...$fieldRules];
            }
        }

        return $rules;
    }
}
