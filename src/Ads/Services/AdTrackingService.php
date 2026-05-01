<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\Services;

use DalmoaCore\Ads\Contracts\AdTrackingInterface;

final class AdTrackingService implements AdTrackingInterface
{
    public function track(int $adId, string $type, ?string $placement = null): void
    {
        if ($adId <= 0) return;

        if ($type === 'click') {
            $this->increment($adId, 'click_count', $placement);
        }

        if ($type === 'impression') {
            $this->increment($adId, 'impression_count', $placement);
        }
    }

    private function increment(int $adId, string $baseKey, ?string $placement): void
    {
        // global count
        $count = (int) get_post_meta($adId, $baseKey, true);
        update_post_meta($adId, $baseKey, $count + 1);

        // placement-specific count
        if ($placement) {
            $key = "{$baseKey}_{$placement}";
            $pCount = (int) get_post_meta($adId, $key, true);
            update_post_meta($adId, $key, $pCount + 1);
        }
    }
}