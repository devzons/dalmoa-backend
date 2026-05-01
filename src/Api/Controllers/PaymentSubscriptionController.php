<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Support\Response;
use DalmoaCore\Support\Payments\AdPlanCatalog;

final class PaymentSubscriptionController
{
    private const AD_POST_TYPE = 'ad_listing';

    public function cancel(\WP_REST_Request $request): \WP_REST_Response
    {
        $postId = (int) $request->get_param('postId');

        if (!$this->isValidAd($postId)) {
            return Response::error('Invalid ad', 400);
        }

        $subscriptionId = (string) get_post_meta($postId, 'stripe_subscription_id', true);

        if ($subscriptionId === '') {
            return Response::error('Subscription not found', 404);
        }

        try {
            $this->bootStripe();

            $subscription = \Stripe\Subscription::update($subscriptionId, [
                'cancel_at_period_end' => true,
            ]);

            $this->sync($postId, $subscription, 'cancel_pending');

            return Response::json([
                'ok' => true,
                'status' => (string) $subscription->status,
                'cancelAtPeriodEnd' => (bool) $subscription->cancel_at_period_end,
                'currentPeriodEnd' => isset($subscription->current_period_end)
                    ? gmdate('c', (int) $subscription->current_period_end)
                    : null,
            ]);
        } catch (\Throwable $e) {
            return Response::error('Failed to cancel subscription', 500);
        }
    }

    public function resume(\WP_REST_Request $request): \WP_REST_Response
    {
        $postId = (int) $request->get_param('postId');

        if (!$this->isValidAd($postId)) {
            return Response::error('Invalid ad', 400);
        }

        $subscriptionId = (string) get_post_meta($postId, 'stripe_subscription_id', true);

        if ($subscriptionId === '') {
            return Response::error('Subscription not found', 404);
        }

        try {
            $this->bootStripe();

            $subscription = \Stripe\Subscription::retrieve($subscriptionId);

            if ((string) $subscription->status === 'canceled') {
                return Response::error('Canceled subscription cannot be resumed', 409);
            }

            $subscription = \Stripe\Subscription::update($subscriptionId, [
                'cancel_at_period_end' => false,
            ]);

            $this->sync($postId, $subscription);

            return Response::json([
                'ok' => true,
                'status' => (string) $subscription->status,
                'cancelAtPeriodEnd' => (bool) $subscription->cancel_at_period_end,
                'currentPeriodEnd' => isset($subscription->current_period_end)
                    ? gmdate('c', (int) $subscription->current_period_end)
                    : null,
            ]);
        } catch (\Throwable $e) {
            return Response::error('Failed to resume subscription', 500);
        }
    }

    public function syncFromStripe(\WP_REST_Request $request): \WP_REST_Response
    {
        $postId = (int) $request->get_param('postId');

        if (!$this->isValidAd($postId)) {
            return Response::error('Invalid ad', 400);
        }

        $subscriptionId = (string) get_post_meta($postId, 'stripe_subscription_id', true);

        if ($subscriptionId === '') {
            return Response::error('Subscription not found', 404);
        }

        try {
            $this->bootStripe();

            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $this->sync($postId, $subscription);

            return Response::json([
                'ok' => true,
                'status' => (string) $subscription->status,
                'cancelAtPeriodEnd' => (bool) $subscription->cancel_at_period_end,
            ]);
        } catch (\Throwable $e) {
            return Response::error('Failed to sync subscription', 500);
        }
    }

    private function sync(int $postId, object $subscription, ?string $forcedPaymentStatus = null): void
    {
        $status = (string) ($subscription->status ?? 'unknown');
        $cancelAtPeriodEnd = (bool) ($subscription->cancel_at_period_end ?? false);
        $currentPeriodStart = isset($subscription->current_period_start) ? (int) $subscription->current_period_start : 0;
        $currentPeriodEnd = isset($subscription->current_period_end) ? (int) $subscription->current_period_end : 0;

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
            update_post_meta($postId, 'payment_status', $forcedPaymentStatus ?? 'cancel_pending');
            update_post_meta($postId, 'subscription_cancelled_at', $this->wpDate(current_time('timestamp')));
            return;
        }

        delete_post_meta($postId, 'subscription_cancelled_at');

        if (in_array($status, ['active', 'trialing'], true)) {
            update_post_meta($postId, 'payment_status', $forcedPaymentStatus ?? 'paid');
            update_post_meta($postId, 'is_paid', '1');
            update_post_meta($postId, 'is_featured', '1');

            $plan = sanitize_key((string) get_post_meta($postId, 'ad_plan', true));

            if ($plan !== '' && AdPlanCatalog::exists($plan)) {
                $planData = AdPlanCatalog::get($plan);
                update_post_meta($postId, 'priority_score', (int) $planData['priority']);
            }

            return;
        }

        if (in_array($status, ['past_due', 'unpaid', 'incomplete', 'incomplete_expired'], true)) {
            update_post_meta($postId, 'payment_status', $forcedPaymentStatus ?? $status);
            return;
        }

        if (in_array($status, ['canceled', 'paused'], true)) {
            update_post_meta($postId, 'payment_status', 'cancelled');
            update_post_meta($postId, 'is_paid', '0');
            update_post_meta($postId, 'is_featured', '0');
            update_post_meta($postId, 'priority_score', '0');
        }
    }

    private function isValidAd(int $postId): bool
    {
        return $postId > 0 && get_post_type($postId) === self::AD_POST_TYPE;
    }

    private function bootStripe(): void
    {
        $secretKey = getenv('STRIPE_SECRET_KEY');

        if (!$secretKey) {
            throw new \RuntimeException('Stripe secret key missing');
        }

        \Stripe\Stripe::setApiKey($secretKey);
    }

    private function wpDate(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}