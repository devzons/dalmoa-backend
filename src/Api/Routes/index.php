<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

final class Routes
{
    public static function register(): void
    {
        AdTrackingRoutes::register();
    }
}