<?php

declare(strict_types=1);

namespace App\Support;

final class UploadLimit
{
    private const MINIMUM_MEGABYTES = 2;

    public static function maxSizeInMegabytes(): int
    {
        $bytes = min(
            self::parseIniSize((string) ini_get('upload_max_filesize')),
            self::parseIniSize((string) ini_get('post_max_size')),
        );

        return max(self::MINIMUM_MEGABYTES, (int) floor($bytes / (1024 * 1024)));
    }

    private static function parseIniSize(string $size): float
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size) ?? '';
        $value = (float) (preg_replace('/[^0-9.]/', '', $size) ?? '0');

        if ($unit === '') {
            return round($value);
        }

        return round($value * pow(1024, stripos('bkmgtpezy', $unit[0])));
    }
}
