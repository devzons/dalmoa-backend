<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\Services;

final class AdRankingService
{
    private const CTR_WEIGHT = 1000;
    private const BID_WEIGHT = 100;
    private const FRESHNESS_WEIGHT = 80;
    private const PREMIUM_BONUS = 300;
    private const FEATURED_BONUS = 200;
    private const PAID_BONUS = 100;

    /**
     * @param \WP_Post[] $posts
     * @return \WP_Post[]
     */
    public function sort(array $posts): array
    {
        usort($posts, function (\WP_Post $a, \WP_Post $b): int {
            return $this->score($b->ID) <=> $this->score($a->ID);
        });

        return $posts;
    }

    public function score(int $adId): int
    {
        $plan = (string) get_post_meta($adId, 'ad_plan', true);
        $isPaid = get_post_meta($adId, 'is_paid', true) === '1';
        $isFeatured = get_post_meta($adId, 'is_featured', true) === '1';

        $bid = max(0.0, (float) get_post_meta($adId, 'bid_amount', true));
        $impressions = max(0, (int) get_post_meta($adId, 'impression_count', true));
        $clicks = max(0, (int) get_post_meta($adId, 'click_count', true));

        $ctr = $impressions > 0 ? $clicks / $impressions : 0.0;

        $planScore = match ($plan) {
            'premium', 'premium_monthly' => self::PREMIUM_BONUS,
            'featured', 'featured_monthly' => self::FEATURED_BONUS,
            default => $isPaid || $isFeatured ? self::PAID_BONUS : 0,
        };

        $ctrScore = (int) round($ctr * self::CTR_WEIGHT);
        $bidScore = (int) round($bid * self::BID_WEIGHT);
        $freshnessScore = $this->freshnessScore($adId);

        return $planScore + $ctrScore + $bidScore + $freshnessScore;
    }

    private function freshnessScore(int $adId): int
    {
        $createdAt = get_post_time('U', true, $adId);

        if (!$createdAt) {
            return 0;
        }

        $ageDays = max(0, (int) floor((time() - $createdAt) / DAY_IN_SECONDS));

        return max(0, self::FRESHNESS_WEIGHT - ($ageDays * 2));
    }
}