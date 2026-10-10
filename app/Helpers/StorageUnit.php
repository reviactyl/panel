<?php

namespace App\Helpers;

class StorageUnit
{
    private const MEBIBYTE = 1024 * 1024;

    public static function isBinary(): bool
    {
        return filter_var(config('panel.use_binary_prefix', true), FILTER_VALIDATE_BOOL);
    }

    public static function base(): int
    {
        return self::isBinary() ? 1024 : 1000;
    }

    public static function megabyte(): string
    {
        return self::isBinary() ? 'MiB' : 'MB';
    }

    public static function gigabyte(): string
    {
        return self::isBinary() ? 'GiB' : 'GB';
    }

    public static function formatMegabytes(int|float $value): string
    {
        if ($value >= self::base()) {
            return round($value / self::base(), 2).' '.self::gigabyte();
        }

        return $value.' '.self::megabyte();
    }

    public static function formatBytes(int|float $bytes, int $decimals = 2): string
    {
        $units = self::isBinary()
            ? ['B', 'KiB', 'MiB', 'GiB', 'TiB']
            : ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max((float) $bytes, 0.0);
        if ($bytes < 1) {
            return '0 B';
        }

        $pow = (int) min(floor(log($bytes) / log(self::base())), count($units) - 1);

        return round($bytes / (self::base() ** $pow), $decimals).' '.$units[$pow];
    }

    public static function toMebibytes(int|float $value): int
    {
        if ($value <= 0 || self::isBinary()) {
            return (int) $value;
        }

        return max(1, (int) round($value * 1000 * 1000 / self::MEBIBYTE));
    }
}
