<?php
declare(strict_types=1);

namespace DalmoaCore\Payments\Webhooks;

final class StripeWebhookHandler
{
    public function handle(array $payload): void
    {
        $type = $payload['type'] ?? '';

        if ($type === 'checkout.session.completed') {
            $this->handleCheckoutCompleted($payload['data']['object'] ?? []);
        }

        if ($type === 'invoice.payment_succeeded') {
            $this->handleSubscriptionRenewal($payload['data']['object'] ?? []);
        }

        if ($type === 'customer.subscription.deleted') {
            $this->handleSubscriptionCancelled($payload['data']['object'] ?? []);
        }
    }

    private function handleCheckoutCompleted(array $session): void
    {
        $postId = (int) ($session['metadata']['postId'] ?? 0);
        $plan = (string) ($session['metadata']['plan'] ?? 'basic');

        if ($postId <= 0) return;

        update_post_meta($postId, 'is_paid', '1');
        update_post_meta($postId, 'ad_plan', $plan);
        update_post_meta($postId, 'is_active', '1');

        update_post_meta($postId, 'ad_starts_at', current_time('mysql'));
        update_post_meta(
            $postId,
            'ad_ends_at',
            date('Y-m-d H:i:s', strtotime('+30 days'))
        );
    }

    private function handleSubscriptionRenewal(array $invoice): void
    {
        $postId = (int) ($invoice['metadata']['postId'] ?? 0);

        if ($postId <= 0) return;

        update_post_meta($postId, 'is_active', '1');

        update_post_meta(
            $postId,
            'ad_ends_at',
            date('Y-m-d H:i:s', strtotime('+30 days'))
        );
    }

    private function handleSubscriptionCancelled(array $subscription): void
    {
        $postId = (int) ($subscription['metadata']['postId'] ?? 0);

        if ($postId <= 0) return;

        update_post_meta($postId, 'is_active', '0');
    }
}