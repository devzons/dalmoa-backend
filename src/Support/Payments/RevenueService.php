<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Payments;

final class RevenueService
{
    public function getSummary(): array
    {
        $oneTimeRevenue = $this->sumOneTimeRevenue();
        $mrr = $this->sumMonthlyRecurringRevenue();

        return [
            'one_time_revenue' => $oneTimeRevenue,
            'mrr' => $mrr,
            'total_monthly_value' => $oneTimeRevenue + $mrr,
            'currency' => 'usd',
        ];
    }

    private function sumOneTimeRevenue(): int
    {
        global $wpdb;

        $value = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT COALESCE(SUM(CAST(amount.meta_value AS UNSIGNED)), 0)
                FROM {$wpdb->postmeta} amount
                INNER JOIN {$wpdb->postmeta} status
                    ON status.post_id = amount.post_id
                    AND status.meta_key = %s
                    AND status.meta_value = %s
                INNER JOIN {$wpdb->postmeta} billing
                    ON billing.post_id = amount.post_id
                    AND billing.meta_key = %s
                    AND billing.meta_value = %s
                WHERE amount.meta_key = %s
                ",
                'payment_status',
                'paid',
                'billing_type',
                'one_time',
                'stripe_amount_total'
            )
        );

        return (int) $value;
    }

    private function sumMonthlyRecurringRevenue(): int
    {
        global $wpdb;

        $value = $wpdb->get_var(
            $wpdb->prepare(
                "
                SELECT COALESCE(SUM(CAST(amount.meta_value AS UNSIGNED)), 0)
                FROM {$wpdb->postmeta} amount
                INNER JOIN {$wpdb->postmeta} status
                    ON status.post_id = amount.post_id
                    AND status.meta_key = %s
                    AND status.meta_value IN ('active', 'trialing')
                INNER JOIN {$wpdb->postmeta} billing
                    ON billing.post_id = amount.post_id
                    AND billing.meta_key = %s
                    AND billing.meta_value = %s
                WHERE amount.meta_key = %s
                ",
                'subscription_status',
                'billing_type',
                'subscription',
                'subscription_amount'
            )
        );

        return (int) $value;
    }
}