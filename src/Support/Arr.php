<?php
declare(strict_types=1);

namespace DalmoaCore\Support;

final class Arr
{
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
}