<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Payments;

final class AdPlanCatalog
{
    private const PLANS = [
        'premium' => [
            'id' => 'premium',
            'label' => 'Premium Ad - 30 Days',
            'amount' => 9900,
            'currency' => 'usd',
            'days' => 30,
            'durationDays' => 30,
            'priority' => 300,
            'priorityScore' => 300,
            'adPlan' => 'premium',
            'type' => 'one_time',
        ],
        'featured' => [
            'id' => 'featured',
            'label' => 'Featured Ad - 14 Days',
            'amount' => 4900,
            'currency' => 'usd',
            'days' => 14,
            'durationDays' => 14,
            'priority' => 200,
            'priorityScore' => 200,
            'adPlan' => 'featured',
            'type' => 'one_time',
        ],
        'premium_monthly' => [
            'id' => 'premium_monthly',
            'label' => 'Premium Ad (Monthly)',
            'amount' => 9900,
            'currency' => 'usd',
            'interval' => 'month',
            'interval_count' => 1,
            'priority' => 300,
            'priorityScore' => 300,
            'adPlan' => 'premium',
            'type' => 'subscription',
        ],
        'featured_monthly' => [
            'id' => 'featured_monthly',
            'label' => 'Featured Ad (Monthly)',
            'amount' => 4900,
            'currency' => 'usd',
            'interval' => 'month',
            'interval_count' => 1,
            'priority' => 200,
            'priorityScore' => 200,
            'adPlan' => 'featured',
            'type' => 'subscription',
        ],
        'basic' => [
            'id' => 'basic',
            'label' => 'Basic Listing',
            'amount' => 0,
            'currency' => 'usd',
            'days' => 0,
            'durationDays' => 0,
            'priority' => 0,
            'priorityScore' => 0,
            'adPlan' => 'basic',
            'type' => 'free',
        ],
    ];

    public static function all(): array
    {
        return self::PLANS;
    }

    public static function get(string $plan): array
    {
        return self::PLANS[$plan] ?? self::PLANS['basic'];
    }

    public static function exists(string $plan): bool
    {
        return isset(self::PLANS[$plan]);
    }

    public static function isPaid(string $plan): bool
    {
        return self::exists($plan) && self::PLANS[$plan]['amount'] > 0;
    }

    public static function isSubscription(string $plan): bool
    {
        return self::exists($plan) && self::PLANS[$plan]['type'] === 'subscription';
    }

    public static function isOneTime(string $plan): bool
    {
        return self::exists($plan) && self::PLANS[$plan]['type'] === 'one_time';
    }

    public static function getPriority(string $plan): int
    {
        return (int) (self::PLANS[$plan]['priority'] ?? 0);
    }

    public static function getPriorityScore(string $plan): int
    {
        return (int) (self::PLANS[$plan]['priorityScore'] ?? self::getPriority($plan));
    }

    public static function getAdPlan(string $plan): string
    {
        return (string) (self::PLANS[$plan]['adPlan'] ?? 'basic');
    }

    public static function getDurationDays(string $plan): int
    {
        return (int) (self::PLANS[$plan]['durationDays'] ?? self::PLANS[$plan]['days'] ?? 0);
    }

    public static function getInterval(string $plan): ?string
    {
        return self::PLANS[$plan]['interval'] ?? null;
    }

    public static function getIntervalCount(string $plan): int
    {
        return (int) (self::PLANS[$plan]['interval_count'] ?? 1);
    }
}