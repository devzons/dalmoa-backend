<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\SearchController;

final class SearchRoutes
{
    public function register(): void
    {
        $controller = new SearchController();

        register_rest_route('dalmoa/v1', '/search', [
            'methods' => \WP_REST_Server::READABLE,
            'callback' => [$controller, 'index'],
            'permission_callback' => '__return_true',
        ]);
    }
}