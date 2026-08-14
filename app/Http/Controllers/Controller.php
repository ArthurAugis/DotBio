<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

abstract class Controller
{
    protected function storePublicFile(Request $request, string $field, string $directory): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        return Storage::url($request->file($field)->store($directory, 'public'));
    }

    protected function deleteOldPublicFile(?string $oldUrl): void
    {
        if (! $oldUrl) {
            return;
        }

        $relativePath = ltrim(str_replace('/storage/', '', parse_url($oldUrl, PHP_URL_PATH) ?? ''), '/');

        if ($relativePath !== '' && Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    protected function commaSeparatedValues(?string $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }

    protected function jsonArrayValue(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function booleanFieldValues(Request $request, array $fields): array
    {
        $values = [];

        foreach ($fields as $field) {
            $values[$field] = $request->boolean($field);
        }

        return $values;
    }
}
