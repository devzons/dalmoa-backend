<?php
declare(strict_types=1);

namespace DalmoaCore\Support;

final class Cast
{
    public static function string(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : (is_scalar($value) ? trim((string) $value) : '');
        return $value !== '' ? $value : null;
    }

    public static function bool(mixed $value): bool
    {
        return (bool) $value;
    }

    public static function int(mixed $value): int
    {
        return (int) $value;
    }
}