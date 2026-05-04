<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\NewsController;

final class NewsRoutes
{
    public function register(): void
    {
        $controller = new NewsController();

        register_rest_route('dalmoa/v1', '/news', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'index'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/news/(?P<slug>[^/]+)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'show'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/news/(?P<id>\d+)/click', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'click'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/news/(?P<id>\d+)/view', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'view'],
            'permission_callback' => '__return_true',
        ]);
    }
}