<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Support\Response;
use DalmoaCore\Support\Payments\AdPlanCatalog;

final class PaymentController
{
    private const PROMOTABLE_POST_TYPES = [
        'ad_listing',
        'directory',
        'business_sale',
        'job',
        'loan',
        'marketplace',
        'real_estate',
        'car',
        'town_board',
        'news',
    ];

    public function createCheckoutSession(\WP_REST_Request $request): \WP_REST_Response
    {
        $postId = (int) $request->get_param('postId');
        $plan = sanitize_key((string) $request->get_param('plan'));
        $locale = $this->normalizeLocale((string) $request->get_param('locale'));

        if (!$this->isValidPromotablePost($postId) || !AdPlanCatalog::exists($plan)) {
            return $this->error('Invalid request', 400);
        }

        if (!AdPlanCatalog::isOneTime($plan)) {
            return $this->error('Invalid one-time plan', 400);
        }

        $planData = AdPlanCatalog::get($plan);

        if ((int) $planData['amount'] <= 0) {
            return $this->error('Invalid paid plan', 400);
        }

        try {
            $this->bootStripe();

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => $planData['currency'],
                        'product_data' => [
                            'name' => $planData['label'],
                        ],
                        'unit_amount' => (int) $planData['amount'],
                    ],
                    'quantity' => 1,
                ]],
                'metadata' => [
                    'post_id' => (string) $postId,
                    'post_type' => (string) get_post_type($postId),
                    'plan' => $plan,
                    'locale' => $locale,
                    'billing_type' => 'one_time',
                ],
                'success_url' => $this->frontendUrl("/{$locale}/payment/success?session_id={CHECKOUT_SESSION_ID}&post_id={$postId}&plan={$plan}"),
                'cancel_url' => $this->frontendUrl("/{$locale}/payment/cancel"),
            ]);

            update_post_meta($postId, 'stripe_session_id', (string) $session->id);
            update_post_meta($postId, 'payment_status', 'pending');
            update_post_meta($postId, 'billing_type', 'one_time');
            update_post_meta($postId, 'pending_ad_plan', $plan);

            return Response::json([
                'url' => $session->url,
                'sessionId' => $session->id,
            ]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function createSubscriptionSession(\WP_REST_Request $request): \WP_REST_Response
    {
        $postId = (int) $request->get_param('postId');
        $plan = sanitize_key((string) $request->get_param('plan'));
        $locale = $this->normalizeLocale((string) $request->get_param('locale'));

        if (!$this->isValidPromotablePost($postId) || !AdPlanCatalog::exists($plan) || !AdPlanCatalog::isSubscription($plan)) {
            return $this->error('Invalid subscription request', 400);
        }

        $existingSubscriptionId = (string) get_post_meta($postId, 'stripe_subscription_id', true);
        $existingStatus = (string) get_post_meta($postId, 'subscription_status', true);

        if ($existingSubscriptionId !== '' && in_array($existingStatus, ['active', 'trialing', 'past_due', 'unpaid'], true)) {
            return $this->error('Subscription already exists', 409);
        }

        $planData = AdPlanCatalog::get($plan);

        try {
            $this->bootStripe();

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'line_items' => [[
                    'price_data' => [
                        'currency' => $planData['currency'],
                        'recurring' => [
                            'interval' => $planData['interval'],
                            'interval_count' => (int) ($planData['interval_count'] ?? 1),
                        ],
                        'product_data' => [
                            'name' => $planData['label'],
                        ],
                        'unit_amount' => (int) $planData['amount'],
                    ],
                    'quantity' => 1,
                ]],
                'metadata' => [
                    'post_id' => (string) $postId,
                    'post_type' => (string) get_post_type($postId),
                    'plan' => $plan,
                    'locale' => $locale,
                    'billing_type' => 'subscription',
                ],
                'subscription_data' => [
                    'metadata' => [
                        'post_id' => (string) $postId,
                        'post_type' => (string) get_post_type($postId),
                        'plan' => $plan,
                        'locale' => $locale,
                        'billing_type' => 'subscription',
                    ],
                ],
                'success_url' => $this->frontendUrl("/{$locale}/payment/success?session_id={CHECKOUT_SESSION_ID}&post_id={$postId}&plan={$plan}&billing=subscription"),
                'cancel_url' => $this->frontendUrl("/{$locale}/payment/cancel"),
            ]);

            update_post_meta($postId, 'stripe_session_id', (string) $session->id);
            update_post_meta($postId, 'payment_status', 'pending');
            update_post_meta($postId, 'billing_type', 'subscription');
            update_post_meta($postId, 'subscription_status', 'pending');
            update_post_meta($postId, 'pending_ad_plan', $plan);

            return Response::json([
                'url' => $session->url,
                'sessionId' => $session->id,
            ]);
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    public function webhook(\WP_REST_Request $request): \WP_REST_Response
    {
        $payload = $request->get_body();
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpointSecret = $this->stripeWebhookSecret();

        if (!$endpointSecret) {
            return $this->error('Webhook secret missing', 500);
        }

        try {
            $this->bootStripe();
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\Throwable $e) {
            return $this->error('Invalid webhook', 400);
        }

        $eventId = (string) ($event->id ?? '');

        $processedKey = '';

        if ($eventId !== '') {
            $processedKey = 'dalmoa_stripe_event_' . md5($eventId);

            if (get_transient($processedKey)) {
                return Response::json(['received' => true, 'duplicate' => true]);
            }
        }

        try {
            switch ((string) $event->type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutCompleted($event->data->object);
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->handleSubscriptionSynced($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;

                case 'invoice.payment_succeeded':
                    $this->handleInvoicePaymentSucceeded($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handleInvoicePaymentFailed($event->data->object);
                    break;
            }
        } catch (\Throwable $e) {
            return $this->error('Webhook handler failed', 500);
        }

        if ($processedKey !== '') {
            set_transient($processedKey, '1', DAY_IN_SECONDS);
        }

        return Response::json(['received' => true]);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        $postId = (int) ($session->metadata->post_id ?? 0);
        $plan = sanitize_key((string) ($session->metadata->plan ?? ''));
        $billingType = (string) ($session->metadata->billing_type ?? 'one_time');

        if (!$this->isValidPromotablePost($postId) || !AdPlanCatalog::exists($plan)) {
            return;
        }

        $now = current_time('timestamp');

        $this->applyPaidPlan($postId, $plan);

        update_post_meta($postId, 'payment_status', 'paid');
        update_post_meta($postId, 'stripe_customer_id', (string) ($session->customer ?? ''));
        update_post_meta($postId, 'stripe_amount_total', (int) ($session->amount_total ?? 0));
        update_post_meta($postId, 'stripe_currency', (string) ($session->currency ?? 'usd'));
        update_post_meta($postId, 'paid_at', $this->wpDate($now));

        delete_post_meta($postId, 'pending_ad_plan');

        if ($billingType === 'subscription') {
            $subscriptionId = (string) ($session->subscription ?? '');

            update_post_meta($postId, 'billing_type', 'subscription');
            update_post_meta($postId, 'stripe_subscription_id', $subscriptionId);
            update_post_meta($postId, 'subscription_started_at', $this->wpDate($now));
            update_post_meta($postId, 'subscription_amount', (int) ($session->amount_total ?? 0));
            update_post_meta($postId, 'subscription_currency', (string) ($session->currency ?? 'usd'));
            update_post_meta($postId, 'ad_starts_at', $this->wpDate($now));

            if ($subscriptionId !== '') {
                try {
                    $this->bootStripe();
                    $subscription = \Stripe\Subscription::retrieve($subscriptionId);
                    $this->syncSubscriptionToAd($postId, $subscription);
                    return;
                } catch (\Throwable $e) {
                    $fallbackEnd = strtotime('+30 days', $now);
                    update_post_meta($postId, 'subscription_status', 'active');
                    update_post_meta($postId, 'ad_ends_at', $this->wpDate($fallbackEnd));
                    update_post_meta($postId, 'expires_at', $this->wpDate($fallbackEnd));
                }
            }

            return;
        }

        $durationDays = AdPlanCatalog::getDurationDays($plan);
        $end = strtotime('+' . $durationDays . ' days', $now);

        update_post_meta($postId, 'billing_type', 'one_time');
        update_post_meta($postId, 'stripe_payment_intent_id', (string) ($session->payment_intent ?? ''));
        update_post_meta($postId, 'ad_starts_at', $this->wpDate($now));
        update_post_meta($postId, 'ad_ends_at', $this->wpDate($end));
        update_post_meta($postId, 'expires_at', $this->wpDate($end));
    }

    private function handleSubscriptionSynced(object $subscription): void
    {
        $postId = $this->resolvePostIdFromSubscription($subscription);

        if (!$this->isValidPromotablePost($postId)) {
            return;
        }

        $this->syncSubscriptionToAd($postId, $subscription);
    }

    private function handleSubscriptionDeleted(object $subscription): void
    {
        $postId = $this->resolvePostIdFromSubscription($subscription);

        if (!$this->isValidPromotablePost($postId)) {
            return;
        }

        $now = current_time('timestamp');

        update_post_meta($postId, 'subscription_status', 'canceled');
        update_post_meta($postId, 'subscription_cancelled_at', $this->wpDate($now));
        update_post_meta($postId, 'payment_status', 'cancelled');
        update_post_meta($postId, 'ad_ends_at', $this->wpDate($now));
        update_post_meta($postId, 'expires_at', $this->wpDate($now));

        $this->deactivateAd($postId);
    }

    private function handleInvoicePaymentSucceeded(object $invoice): void
    {
        $subscriptionId = (string) ($invoice->subscription ?? '');

        if ($subscriptionId === '') {
            return;
        }

        $postId = $this->findPostIdByMeta('stripe_subscription_id', $subscriptionId);

        if (!$this->isValidPromotablePost($postId)) {
            return;
        }

        update_post_meta($postId, 'payment_status', 'paid');
        update_post_meta($postId, 'last_invoice_id', (string) ($invoice->id ?? ''));
        update_post_meta($postId, 'last_payment_at', $this->wpDate(current_time('timestamp')));
        update_post_meta($postId, 'subscription_amount', (int) ($invoice->amount_paid ?? 0));
        update_post_meta($postId, 'subscription_currency', (string) ($invoice->currency ?? 'usd'));

        try {
            $this->bootStripe();
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $this->syncSubscriptionToAd($postId, $subscription);
        } catch (\Throwable $e) {
            $end = strtotime('+30 days', current_time('timestamp'));
            update_post_meta($postId, 'is_active', '1');
            update_post_meta($postId, 'is_paid', '1');
            update_post_meta($postId, 'is_featured', '1');
            update_post_meta($postId, 'ad_ends_at', $this->wpDate($end));
            update_post_meta($postId, 'expires_at', $this->wpDate($end));
        }
    }

    private function handleInvoicePaymentFailed(object $invoice): void
    {
        $subscriptionId = (string) ($invoice->subscription ?? '');

        if ($subscriptionId === '') {
            return;
        }

        $postId = $this->findPostIdByMeta('stripe_subscription_id', $subscriptionId);

        if (!$this->isValidPromotablePost($postId)) {
            return;
        }

        update_post_meta($postId, 'payment_status', 'payment_failed');
        update_post_meta($postId, 'subscription_status', 'past_due');
        update_post_meta($postId, 'last_failed_invoice_id', (string) ($invoice->id ?? ''));
        update_post_meta($postId, 'last_payment_failed_at', $this->wpDate(current_time('timestamp')));
    }

    private function syncSubscriptionToAd(int $postId, object $subscription): void
    {
        $status = (string) ($subscription->status ?? 'unknown');
        $cancelAtPeriodEnd = (bool) ($subscription->cancel_at_period_end ?? false);
        $currentPeriodStart = isset($subscription->current_period_start) ? (int) $subscription->current_period_start : 0;
        $currentPeriodEnd = isset($subscription->current_period_end) ? (int) $subscription->current_period_end : 0;
        $plan = sanitize_key((string) ($subscription->metadata->plan ?? get_post_meta($postId, 'pending_ad_plan', true) ?: get_post_meta($postId, 'ad_plan', true)));

        update_post_meta($postId, 'billing_type', 'subscription');
        update_post_meta($postId, 'stripe_subscription_id', (string) ($subscription->id ?? ''));
        update_post_meta($postId, 'subscription_status', $status);
        update_post_meta($postId, 'subscription_cancel_at_period_end', $cancelAtPeriodEnd ? '1' : '0');

        if ($currentPeriodStart > 0) {
            update_post_meta($postId, 'subscription_current_period_start', $this->wpDate($currentPeriodStart));
        }

        if ($currentPeriodEnd > 0) {
            update_post_meta($postId, 'subscription_current_period_end', $this->wpDate($currentPeriodEnd));
            update_post_meta($postId, 'ad_ends_at', $this->wpDate($currentPeriodEnd));
            update_post_meta($postId, 'expires_at', $this->wpDate($currentPeriodEnd));
        }

        if ($cancelAtPeriodEnd) {
            update_post_meta($postId, 'payment_status', 'cancel_pending');
            update_post_meta($postId, 'subscription_cancelled_at', $this->wpDate(current_time('timestamp')));
            return;
        }

        delete_post_meta($postId, 'subscription_cancelled_at');

        if (in_array($status, ['active', 'trialing'], true)) {
            update_post_meta($postId, 'payment_status', 'paid');

            if (AdPlanCatalog::exists($plan)) {
                $this->applyPaidPlan($postId, $plan);
            }

            return;
        }

        if (in_array($status, ['past_due', 'unpaid', 'incomplete'], true)) {
            update_post_meta($postId, 'payment_status', $status);
            return;
        }

        if (in_array($status, ['canceled', 'paused', 'incomplete_expired'], true)) {
            update_post_meta($postId, 'payment_status', 'cancelled');
            $this->deactivateAd($postId);
        }
    }

    private function applyPaidPlan(int $postId, string $plan): void
    {
        update_post_meta($postId, 'ad_plan', AdPlanCatalog::getAdPlan($plan));
        update_post_meta($postId, 'purchased_plan', $plan);
        update_post_meta($postId, 'priority_score', (string) AdPlanCatalog::getPriorityScore($plan));
        update_post_meta($postId, 'is_active', '1');
        update_post_meta($postId, 'is_paid', '1');
        update_post_meta($postId, 'is_featured', '1');
    }

    private function deactivateAd(int $postId): void
    {
        update_post_meta($postId, 'is_paid', '0');
        update_post_meta($postId, 'is_featured', '0');
        update_post_meta($postId, 'priority_score', '0');
        update_post_meta($postId, 'ad_plan', 'basic');

        delete_post_meta($postId, 'pending_ad_plan');
    }

    private function resolvePostIdFromSubscription(object $subscription): int
    {
        $postId = (int) ($subscription->metadata->post_id ?? 0);

        if ($postId > 0) {
            return $postId;
        }

        $subscriptionId = (string) ($subscription->id ?? '');

        if ($subscriptionId === '') {
            return 0;
        }

        return $this->findPostIdByMeta('stripe_subscription_id', $subscriptionId);
    }

    private function findPostIdByMeta(string $key, string $value): int
    {
        if ($value === '') {
            return 0;
        }

        $posts = get_posts([
            'post_type' => self::PROMOTABLE_POST_TYPES,
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => 1,
            'meta_query' => [[
                'key' => $key,
                'value' => $value,
                'compare' => '=',
            ]],
        ]);

        return !empty($posts) ? (int) $posts[0] : 0;
    }

    private function isValidPromotablePost(int $postId): bool
    {
        return $postId > 0 && in_array((string) get_post_type($postId), self::PROMOTABLE_POST_TYPES, true);
    }

    private function normalizeLocale(string $locale): string
    {
        return $locale === 'en' ? 'en' : 'ko';
    }

    private function bootStripe(): void
    {
        $secretKey = $this->stripeSecretKey();

        if (!$secretKey) {
            throw new \RuntimeException('Stripe secret key missing');
        }

        \Stripe\Stripe::setApiKey($secretKey);
    }

    private function stripeSecretKey(): string
    {
        if (defined('DALMOA_STRIPE_SECRET_KEY')) {
            return (string) DALMOA_STRIPE_SECRET_KEY;
        }

        return (string) getenv('STRIPE_SECRET_KEY');
    }

    private function stripeWebhookSecret(): string
    {
        if (defined('DALMOA_STRIPE_WEBHOOK_SECRET')) {
            return (string) DALMOA_STRIPE_WEBHOOK_SECRET;
        }

        return (string) getenv('STRIPE_WEBHOOK_SECRET');
    }

    private function frontendUrl(string $path): string
    {
        $baseUrl = defined('DALMOA_FRONTEND_URL')
            ? (string) DALMOA_FRONTEND_URL
            : home_url();

        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function wpDate(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    private function error(string $message, int $status): \WP_REST_Response
    {
        return new \WP_REST_Response([
            'message' => $message,
        ], $status);
    }
}
