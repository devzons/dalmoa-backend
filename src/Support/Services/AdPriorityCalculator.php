<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Services;

use DalmoaCore\Support\Payments\AdPlanCatalog;

final class AdPriorityCalculator
{
    public function isFeatured(int $postId): bool
    {
        return get_post_meta($postId, 'is_featured', true) === '1';
    }

    public function isPaid(int $postId): bool
    {
        return get_post_meta($postId, 'is_paid', true) === '1';
    }

    public function isActive(int $postId): bool
    {
        if (get_post_meta($postId, 'is_active', true) === '0') {
            return false;
        }

        if ($this->isScheduled($postId)) {
            return false;
        }

        if ($this->isSubscription($postId)) {
            return $this->isActiveSubscription($postId);
        }

        return !$this->isExpired($postId);
    }

    public function isScheduled(int $postId): bool
    {
        $startsAt = (string) get_post_meta($postId, 'ad_starts_at', true);

        if ($startsAt === '') {
            return false;
        }

        $timestamp = strtotime($startsAt);

        return $timestamp !== false && $timestamp > current_time('timestamp');
    }

    public function isExpired(int $postId): bool
    {
        $endsAt = (string) get_post_meta($postId, 'ad_ends_at', true);
        $expiresAt = (string) get_post_meta($postId, 'expires_at', true);

        $effectiveEndsAt = $endsAt !== '' ? $endsAt : $expiresAt;

        if ($effectiveEndsAt === '') {
            return false;
        }

        $timestamp = strtotime($effectiveEndsAt);

        return $timestamp !== false && $timestamp < current_time('timestamp');
    }

    public function plan(int $postId): string
    {
        $plan = sanitize_key((string) get_post_meta($postId, 'ad_plan', true));

        return AdPlanCatalog::exists($plan) ? $plan : 'basic';
    }

    public function priority(int $postId): int
    {
        if (!$this->isActive($postId)) {
            return 0;
        }

        $plan = $this->plan($postId);

        if (AdPlanCatalog::isPaid($plan)) {
            return AdPlanCatalog::getPriority($plan);
        }

        return $this->isPaid($postId) || $this->isFeatured($postId) ? 100 : 0;
    }

    private function isSubscription(int $postId): bool
    {
        $billingType = (string) get_post_meta($postId, 'billing_type', true);
        $plan = $this->plan($postId);

        return $billingType === 'subscription' || AdPlanCatalog::isSubscription($plan);
    }

    private function isActiveSubscription(int $postId): bool
    {
        $status = (string) get_post_meta($postId, 'subscription_status', true);

        if (in_array($status, ['active', 'trialing'], true)) {
            return true;
        }

        if ($status === 'past_due') {
            return false;
        }

        if (in_array($status, ['unpaid', 'canceled', 'cancelled', 'paused', 'incomplete', 'incomplete_expired'], true)) {
            return false;
        }

        return false;
    }
}