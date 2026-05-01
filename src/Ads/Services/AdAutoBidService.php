<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\Services;

final class AdAutoBidService
{
    public function adjust(int $adId): float
    {
        $impressions = (int) get_post_meta($adId, 'impression_count', true);
        $clicks = (int) get_post_meta($adId, 'click_count', true);
        $currentBid = (float) get_post_meta($adId, 'bid_amount', true);

        if ($currentBid <= 0) {
            $currentBid = 1.0;
        }

        $ctr = $impressions > 0 ? ($clicks / $impressions) : 0;

        // 🎯 목표 CTR (예: 5%)
        $targetCTR = 0.05;

        // 🔥 로직
        if ($ctr > $targetCTR) {
            $newBid = $currentBid * 1.1; // 잘되면 올림
        } elseif ($ctr < $targetCTR / 2) {
            $newBid = $currentBid * 0.85; // 너무 낮으면 내림
        } else {
            $newBid = $currentBid;
        }

        // 최소/최대 제한
        $newBid = max(0.1, min($newBid, 50));

        update_post_meta($adId, 'bid_amount', round($newBid, 2));

        return $newBid;
    }
}