<?php
declare(strict_types=1);

namespace DalmoaCore\Payments\Routes;

use DalmoaCore\Payments\Webhooks\StripeWebhookHandler;

final class StripeWebhookRoute
{
    public function register(): void
    {
        register_rest_route('dalmoa/v1', '/stripe/webhook', [
            'methods' => 'POST',
            'callback' => [$this, 'handle'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        $payload = json_decode($request->get_body(), true);

        if (!is_array($payload)) {
            return new \WP_REST_Response(['error' => 'Invalid payload'], 400);
        }

        (new StripeWebhookHandler())->handle($payload);

        return new \WP_REST_Response(['ok' => true], 200);
    }
}