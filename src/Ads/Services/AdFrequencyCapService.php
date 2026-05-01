<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\Services;

final class AdFrequencyCapService
{
    private const MAX_IMPRESSIONS_PER_DAY = 1000;

    public function filter(array $posts): array
    {
        return array_values(array_filter($posts, function (\WP_Post $post) {
            $todayKey = 'impression_count_' . date('Ymd');
            $count = (int) get_post_meta($post->ID, $todayKey, true);

            return $count < self::MAX_IMPRESSIONS_PER_DAY;
        }));
    }
}