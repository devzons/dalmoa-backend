<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\SubmitController;

final class SubmitRoutes
{
    public function register(): void
    {
        register_rest_route('dalmoa/v1', '/submit/(?P<type>[a-z\-]+)', [
            'methods' => 'POST',
            'callback' => [new SubmitController(), 'store'],
            'permission_callback' => '__return_true', 
        ]);
    }
}