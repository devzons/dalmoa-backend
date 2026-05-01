<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Services;

trait SortsFeaturedListings
{
    private function sortByFeaturedPriority(array $posts): array
    {
        usort($posts, function (\WP_Post $a, \WP_Post $b): int {
            $aScore = $this->featuredScore($a->ID);
            $bScore = $this->featuredScore($b->ID);

            if ($aScore !== $bScore) {
                return $bScore <=> $aScore;
            }

            return strtotime($b->post_date) <=> strtotime($a->post_date);
        });

        return $posts;
    }

    private function featuredScore(int $postId): int
    {
        $adPlan = strtolower((string) get_post_meta($postId, 'ad_plan', true));
        $priority = (int) get_post_meta($postId, 'priority_score', true);

        $isPaid = $this->truthy(get_post_meta($postId, 'is_paid', true));
        $isFeatured = $this->truthy(get_post_meta($postId, 'is_featured', true));
        $isAdActive = get_post_meta($postId, 'is_active', true);

        if ($isAdActive === '0') {
            return 0;
        }

        if ($adPlan === 'premium') {
            return 1000 + $priority;
        }

        if ($adPlan === 'featured') {
            return 500 + $priority;
        }

        if ($isPaid && $priority > 0) {
            return 300 + $priority;
        }

        if ($isFeatured) {
            return 100 + $priority;
        }

        return $priority;
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'on', 'paid'], true);
    }
}