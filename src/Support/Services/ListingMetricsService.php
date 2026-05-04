<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Services;

final class ListingMetricsService
{
    public function incrementClick(int $postId, string $postType): int
    {
        if ($postId <= 0 || get_post_type($postId) !== $postType) {
            return 0;
        }

        $current = (int) get_post_meta($postId, 'click_count', true);
        $next = $current + 1;

        update_post_meta($postId, 'click_count', $next);

        return $next;
    }

    public function incrementView(int $postId, string $postType): int
    {
        if ($postId <= 0 || get_post_type($postId) !== $postType) {
            return 0;
        }

        $current = (int) get_post_meta($postId, 'view_count', true);
        $next = $current + 1;

        update_post_meta($postId, 'view_count', $next);

        return $next;
    }
}