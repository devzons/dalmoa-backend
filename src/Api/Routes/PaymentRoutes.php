<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Routes;

use DalmoaCore\Api\Controllers\PaymentController;
use DalmoaCore\Api\Controllers\PaymentSubscriptionController;
use DalmoaCore\Support\Payments\RevenueService;

final class PaymentRoutes
{
    public function register(): void
    {
        $paymentController = new PaymentController();
        $subscriptionController = new PaymentSubscriptionController();

        // One-time payment
        register_rest_route('dalmoa/v1', '/payments/create-checkout-session', [
            'methods' => 'POST',
            'callback' => [$paymentController, 'createCheckoutSession'],
            'permission_callback' => '__return_true',
        ]);

        // Subscription create
        register_rest_route('dalmoa/v1', '/payments/create-subscription-session', [
            'methods' => 'POST',
            'callback' => [$paymentController, 'createSubscriptionSession'],
            'permission_callback' => '__return_true',
        ]);

        // Webhook
        register_rest_route('dalmoa/v1', '/payments/webhook', [
            'methods' => 'POST',
            'callback' => [$paymentController, 'webhook'],
            'permission_callback' => '__return_true',
        ]);

        // Subscription cancel
        register_rest_route('dalmoa/v1', '/subscriptions/cancel', [
            'methods' => 'POST',
            'callback' => [$subscriptionController, 'cancel'],
            'permission_callback' => '__return_true',
        ]);

        // Subscription resume
        register_rest_route('dalmoa/v1', '/subscriptions/resume', [
            'methods' => 'POST',
            'callback' => [$subscriptionController, 'resume'],
            'permission_callback' => '__return_true',
        ]);

        // Manual sync (admin/debug)
        register_rest_route('dalmoa/v1', '/subscriptions/sync', [
            'methods' => 'POST',
            'callback' => [$subscriptionController, 'syncFromStripe'],
            'permission_callback' => '__return_true',
        ]);

        register_rest_route('dalmoa/v1', '/admin/revenue', [
            'methods' => 'GET',
            'callback' => function () {
                return rest_ensure_response(
                    (new RevenueService())->getSummary()
                );
            },
            'permission_callback' => '__return_true',
        ]);
    }
}