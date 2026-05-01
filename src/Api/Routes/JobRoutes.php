<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\JobController;

final class JobRoutes
{
    public function register(): void
    {
        $controller = new JobController();

        register_rest_route('dalmoa/v1', '/jobs', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'index'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/jobs/(?P<slug>[^/]+)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'show'],
            'permission_callback' => '__return_true',
        ]);
    }
}