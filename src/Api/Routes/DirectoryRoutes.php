<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\DirectoryController;

final class DirectoryRoutes
{
    public function register(): void
    {
        $controller = new DirectoryController();

        register_rest_route('dalmoa/v1', '/directory', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'index'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/directory/(?P<slug>[^/]+)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'show'],
            'permission_callback' => '__return_true',
        ]);
    }
}