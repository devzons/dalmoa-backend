<?php
declare(strict_types=1);

namespace DalmoaCore\Support;

final class Str
{
    public static function value(?string $value, string $fallback = ''): string
    {
        $value = is_string($value) ? trim($value) : '';
        return $value !== '' ? $value : $fallback;
    }
}