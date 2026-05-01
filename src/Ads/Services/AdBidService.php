<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\Services;

final class AdBidService
{
    public function getBid(int $adId): float
    {
        $bid = (float) get_post_meta($adId, 'bid_amount', true);

        if ($bid > 0) {
            return $bid;
        }

        // fallback (plan 기반 기본 입찰가)
        $plan = (string) get_post_meta($adId, 'ad_plan', true);

        return match ($plan) {
            'premium' => 5.0,
            'featured' => 2.0,
            default => 0.5,
        };
    }
}