<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Hooks;

use DalmoaCore\Support\Payments\AdPlanCatalog;

final class AdLifecycleHook
{
    private bool $isRunning = false;

    public function register(): void
    {
        add_action('save_post_ad_listing', [$this, 'handleSave'], 20, 2);
        add_action('init', [$this, 'scheduleCron']);
        add_action('dalmoa_expire_ads_hourly', [$this, 'expireAds']);
    }

    public function scheduleCron(): void
    {
        if (!wp_next_scheduled('dalmoa_expire_ads_hourly')) {
            wp_schedule_event(time(), 'hourly', 'dalmoa_expire_ads_hourly');
        }
    }

    public function handleSave(int $postId, \WP_Post $post): void
    {
        if ($this->isRunning || wp_is_post_revision($postId) || $post->post_type !== 'ad_listing') {
            return;
        }

        $this->isRunning = true;

        try {
            $this->normalizeAdMeta($postId);
            $this->applyCurrentLifecycleState($postId);
        } finally {
            $this->isRunning = false;
        }
    }

    public function expireAds(): void
    {
        $now = current_time('mysql');

        $posts = get_posts([
            'post_type' => 'ad_listing',
            'post_status' => 'publish',
            'fields' => 'ids',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'OR',

                [
                    'relation' => 'AND',
                    [
                        'key' => 'ad_ends_at',
                        'value' => $now,
                        'compare' => '<',
                        'type' => 'DATETIME',
                    ],
                    [
                        'relation' => 'OR',
                        [
                            'key' => 'billing_type',
                            'compare' => 'NOT EXISTS',
                        ],
                        [
                            'key' => 'billing_type',
                            'value' => 'subscription',
                            'compare' => '!=',
                        ],
                        [
                            'key' => 'subscription_status',
                            'value' => ['canceled', 'cancelled', 'incomplete_expired', 'unpaid'],
                            'compare' => 'IN',
                        ],
                    ],
                ],

                [
                    'key' => 'payment_status',
                    'value' => ['expired', 'cancelled', 'canceled', 'payment_failed', 'unpaid', 'incomplete_expired'],
                    'compare' => 'IN',
                ],

                [
                    'key' => 'is_active',
                    'value' => '0',
                    'compare' => '=',
                ],
            ],
        ]);

        foreach ($posts as $postId) {
            $this->deactivateAd((int) $postId, 'expired');
        }
    }

    private function normalizeAdMeta(int $postId): void
    {
        $plan = sanitize_key((string) get_post_meta($postId, 'ad_plan', true));

        if ($plan === '' || !AdPlanCatalog::exists($plan)) {
            $plan = 'basic';
            update_post_meta($postId, 'ad_plan', $plan);
        }

        $billingType = (string) get_post_meta($postId, 'billing_type', true);

        if ($billingType === '') {
            $billingType = AdPlanCatalog::isSubscription($plan) ? 'subscription' : 'one_time';
            update_post_meta($postId, 'billing_type', $billingType);
        }

        $now = current_time('timestamp');

        if ((string) get_post_meta($postId, 'ad_starts_at', true) === '') {
            update_post_meta($postId, 'ad_starts_at', $this->wpDate($now));
        }

        if ($billingType !== 'subscription' && (string) get_post_meta($postId, 'ad_ends_at', true) === '') {
            $days = max(AdPlanCatalog::getDurationDays($plan), 7);
            $endsAt = strtotime("+{$days} days", $now);

            update_post_meta($postId, 'ad_ends_at', $this->wpDate($endsAt));
            update_post_meta($postId, 'expires_at', $this->wpDate($endsAt));
        }
    }

    private function applyCurrentLifecycleState(int $postId): void
    {
        $plan = sanitize_key((string) get_post_meta($postId, 'ad_plan', true));
        $billingType = (string) get_post_meta($postId, 'billing_type', true);
        $paymentStatus = (string) get_post_meta($postId, 'payment_status', true);
        $subscriptionStatus = (string) get_post_meta($postId, 'subscription_status', true);
        $endsAt = (string) get_post_meta($postId, 'ad_ends_at', true);

        if ($this->isExpired($endsAt)) {
            if ($billingType !== 'subscription' || in_array($subscriptionStatus, ['canceled', 'cancelled', 'unpaid', 'incomplete_expired'], true)) {
                $this->deactivateAd($postId, 'expired');
                return;
            }
        }

        if (in_array($paymentStatus, ['expired', 'cancelled', 'canceled', 'payment_failed', 'unpaid', 'incomplete_expired'], true)) {
            $this->deactivateAd($postId, $paymentStatus);
            return;
        }

        if ($billingType === 'subscription') {
            if (in_array($subscriptionStatus, ['active', 'trialing'], true)) {
                $this->activatePaidAd($postId, AdPlanCatalog::getPriority($plan));
                return;
            }

            if (in_array($subscriptionStatus, ['past_due', 'unpaid', 'incomplete', 'incomplete_expired', 'canceled', 'cancelled', 'paused'], true)) {
                $this->deactivateAd($postId, $subscriptionStatus);
                return;
            }
        }

        if (AdPlanCatalog::isPaid($plan) && $paymentStatus === 'paid') {
            $this->activatePaidAd($postId, AdPlanCatalog::getPriority($plan));
            return;
        }

        $this->activateBasicAd($postId);
    }

    private function activatePaidAd(int $postId, int $priority): void
    {
        update_post_meta($postId, 'is_active', '1');
        update_post_meta($postId, 'is_paid', '1');
        update_post_meta($postId, 'is_featured', '1');
        update_post_meta($postId, 'priority_score', $priority);
        update_post_meta($postId, 'payment_status', 'paid');
    }

    private function activateBasicAd(int $postId): void
    {
        update_post_meta($postId, 'is_active', '1');
        update_post_meta($postId, 'is_paid', '0');
        update_post_meta($postId, 'is_featured', '0');
        update_post_meta($postId, 'priority_score', 0);

        if ((string) get_post_meta($postId, 'payment_status', true) === '') {
            update_post_meta($postId, 'payment_status', 'none');
        }
    }

    private function deactivateAd(int $postId, string $reason): void
    {
        update_post_meta($postId, 'is_active', '0');
        update_post_meta($postId, 'is_paid', '0');
        update_post_meta($postId, 'is_featured', '0');
        update_post_meta($postId, 'priority_score', 0);

        if ($reason !== '') {
            update_post_meta($postId, 'payment_status', $reason);
        }
    }

    private function isExpired(string $date): bool
    {
        if ($date === '') {
            return false;
        }

        $timestamp = strtotime($date);

        return $timestamp !== false && $timestamp < current_time('timestamp');
    }

    private function wpDate(int $timestamp): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }
}