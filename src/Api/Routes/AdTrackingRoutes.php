<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\AdTrackingController;

final class AdTrackingRoutes
{
    public static function register(): void
    {
        $controller = new AdTrackingController();

        register_rest_route('dalmoa/v1', '/ads/track/click', [
            'methods' => 'POST',
            'callback' => [$controller, 'trackClick'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/ads/track/impression', [
            'methods' => 'POST',
            'callback' => [$controller, 'trackImpression'],
            'permission_callback' => '__return_true',
        ]);
    }
}