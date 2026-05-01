<?php
declare(strict_types=1);

namespace DalmoaCore\Support;

final class SlugHelper
{
    public static function normalize(string $slug): string
    {
        $decoded = urldecode($slug);

        return sanitize_title($decoded);
    }
}