<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\BusinessSaleController;

final class BusinessSaleRoutes
{
    public function register(): void
    {
        $controller = new BusinessSaleController();

        register_rest_route('dalmoa/v1', '/business-sale', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'index'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/business-sale/(?P<slug>[^/]+)', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'show'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/business-sale/(?P<id>\d+)/click', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'click'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/business-sale/(?P<id>\d+)/view', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$controller, 'view'],
            'permission_callback' => '__return_true',
        ]);
    }
}