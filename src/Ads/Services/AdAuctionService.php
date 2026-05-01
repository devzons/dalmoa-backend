<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\Services;

final class AdAuctionService
{
    private AdBidService $bid;

    public function __construct()
    {
        $this->bid = new AdBidService();
    }

    public function score(int $adId): float
    {
        $priority = (float) get_post_meta($adId, 'priority_score', true);
        $impressions = (int) get_post_meta($adId, 'impression_count', true);
        $clicks = (int) get_post_meta($adId, 'click_count', true);

        $ctr = $impressions > 0 ? ($clicks / $impressions) : 0.0;

        $bid = $this->bid->getBid($adId);
        $freshness = $this->freshness($adId);

        return ($bid * 0.5) + ($priority * 0.2) + ($ctr * 100 * 0.2) + ($freshness * 0.1);
    }

    private function freshness(int $adId): float
    {
        $created = strtotime(get_post_field('post_date', $adId));
        $now = time();

        if (!$created) return 0.0;

        $days = ($now - $created) / 86400;

        return max(0.0, 1 - ($days / 30));
    }

    public function sort(array $posts): array
    {
        usort($posts, function ($a, $b) {
            $scoreA = $this->score($a->ID);
            $scoreB = $this->score($b->ID);

            return $scoreB <=> $scoreA;
        });

        return $posts;
    }
}