<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Tools;

use DalmoaCore\Support\Payments\AdPlanCatalog;
use DalmoaCore\Support\Services\AdPriorityCalculator;

final class AdMetaRepair
{
    public function run(): array
    {
        $calculator = new AdPriorityCalculator();

        $posts = get_posts([
            'post_type' => 'ad_listing',
            'post_status' => 'any',
            'fields' => 'ids',
            'posts_per_page' => -1,
        ]);

        $updated = 0;
        $skipped = 0;

        foreach ($posts as $postId) {
            $postId = (int) $postId;

            if ($postId <= 0) {
                $skipped++;
                continue;
            }

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

            $paymentStatus = (string) get_post_meta($postId, 'payment_status', true);
            $subscriptionStatus = (string) get_post_meta($postId, 'subscription_status', true);

            if ($paymentStatus === '') {
                update_post_meta($postId, 'payment_status', AdPlanCatalog::isPaid($plan) ? 'pending' : 'none');
            }

            if ($billingType === 'subscription' && $subscriptionStatus === '') {
                update_post_meta($postId, 'subscription_status', 'pending');
            }

            $isActive = $calculator->isActive($postId);
            $priority = $calculator->priority($postId);

            update_post_meta($postId, 'is_active', $isActive ? '1' : '0');
            update_post_meta($postId, 'priority_score', $priority);

            if ($priority > 0) {
                update_post_meta($postId, 'is_paid', '1');
                update_post_meta($postId, 'is_featured', '1');
            } else {
                if ($plan === 'basic') {
                    update_post_meta($postId, 'is_paid', '0');
                    update_post_meta($postId, 'is_featured', '0');
                }
            }

            if (!$isActive) {
                update_post_meta($postId, 'is_paid', '0');
                update_post_meta($postId, 'is_featured', '0');
                update_post_meta($postId, 'priority_score', 0);
            }

            $updated++;
        }

        return [
            'ok' => true,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }
}