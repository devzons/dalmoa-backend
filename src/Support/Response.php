<?php
declare(strict_types=1);

namespace DalmoaCore\Support;

final class Response
{
    public static function json(mixed $data, int $status = 200): \WP_REST_Response
    {
        return new \WP_REST_Response($data, $status);
    }

    public static function notFound(string $message = 'Not found'): \WP_Error
    {
        return new \WP_Error('dalmoa_not_found', $message, ['status' => 404]);
    }
}