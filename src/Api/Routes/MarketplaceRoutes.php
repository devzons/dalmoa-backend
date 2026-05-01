<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\MarketplaceController;

final class MarketplaceRoutes
{
    public function register(): void
    {
        $controller = new MarketplaceController();

        register_rest_route('dalmoa/v1', '/marketplace', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'index'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/marketplace/(?P<slug>[^/]+)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'show'],
            'permission_callback' => '__return_true',
        ]);
    }
}