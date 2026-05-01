<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\AdController;

final class AdRoutes
{
    public function register(): void
    {
        $controller = new AdController();

        register_rest_route('dalmoa/v1', '/ads', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'index'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/ads/featured', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'featured'],
            'permission_callback' => '__return_true',
        ]);

        // ✅ sidebar 전용 API
        register_rest_route('dalmoa/v1', '/ads/sidebar', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'sidebar'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/ads/(?P<slug>[^/]+)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'show'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/ads/track', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'track'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/ads/(?P<id>\d+)/click', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'click'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/ads/(?P<id>\d+)/impression', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'impression'],
            'permission_callback' => '__return_true',
        ]);
    }
}