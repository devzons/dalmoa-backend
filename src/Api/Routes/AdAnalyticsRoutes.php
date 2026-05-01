<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\AdAnalyticsController;

final class AdAnalyticsRoutes
{
    public function register(): void
    {
        $controller = new AdAnalyticsController();

        register_rest_route('dalmoa/v1', '/ads/analytics/summary', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'summary'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/ads/analytics/breakdown', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'breakdown'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/ads/analytics/variants', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'variants'],
            'permission_callback' => '__return_true',
        ]);
    }
}