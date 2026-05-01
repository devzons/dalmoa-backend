<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Services;

use DalmoaCore\Query\AdQuery;
use DalmoaCore\Ads\Services\AdAuctionService;
use DalmoaCore\Ads\Services\AdFrequencyCapService;
use DalmoaCore\Ads\Services\AdAutoBidService;
use DalmoaCore\Ads\AbTesting\Repositories\AdVariantRepository;
use DalmoaCore\Ads\AbTesting\Services\AdAbTestService;
use DalmoaCore\Ads\AbTesting\Services\AdVariantSelector;
use DalmoaCore\Ads\Services\AdRankingService;

final class AdService
{
    public function __construct(
        private readonly AdQuery $query = new AdQuery(),
        private readonly AdAuctionService $auction = new AdAuctionService(),
        private readonly AdFrequencyCapService $cap = new AdFrequencyCapService(),
        private readonly AdAutoBidService $autoBid = new AdAutoBidService(),
        private readonly AdAbTestService $abTest = new AdAbTestService(
            new AdVariantRepository(),
            new AdVariantSelector()
        ),
    ) {}

    public function list(array $filters = []): array
    {
        $posts = $this->query->listActive($filters);

        foreach ($posts as $post) {
            $this->autoBid->adjust($post->ID);
        }

        $featured = [];
        $standard = [];

        foreach ($posts as $post) {
            if (!$this->isVisible($post->ID)) {
                continue;
            }

            if ($this->isPromoted($post->ID)) {
                $featured[] = $post;
            } else {
                $standard[] = $post;
            }
        }

        $ranking = new AdRankingService();

        $featured = $ranking->sort($this->auction->sort($featured));
        $standard = $ranking->sort($this->auction->sort($standard));

        $featured = $this->cap->filter($featured);
        $standard = $this->cap->filter($standard);

        return [
            'featured' => $this->applyAbTest($featured),
            'standard' => $this->applyAbTest($standard),
        ];
    }

    public function featured(array $filters = []): array
    {
        return $this->list($filters)['featured'];
    }

    public function sidebar(array $filters = []): ?\WP_Post
    {
        $grouped = $this->list(array_merge($filters, [
            'placement' => 'sidebar_right',
        ]));

        $items = array_values(array_merge($grouped['featured'], $grouped['standard']));

        if ($items === []) {
            return null;
        }

        // 🔥 premium-only first
        $premium = array_filter($items, function (\WP_Post $post) {
            $plan = (string) get_post_meta($post->ID, 'ad_plan', true);
            return in_array($plan, ['premium', 'premium_monthly'], true);
        });

        $pool = $premium !== [] ? array_values($premium) : $items;

        usort($pool, function (\WP_Post $a, \WP_Post $b): int {
            return $this->sidebarScore($b->ID) <=> $this->sidebarScore($a->ID);
        });

        return $pool[0] ?? null;
    }

    public function search(string $q): array
    {
        $grouped = $this->list(['q' => $q]);
        return array_values(array_merge($grouped['featured'], $grouped['standard']));
    }

    public function findBySlug(string $slug): ?\WP_Post
    {
        $post = $this->query->findBySlug($slug);

        if (!$post instanceof \WP_Post) {
            return null;
        }

        return $this->applyAbTest([$post])[0] ?? $post;
    }

    private function applyAbTest(array $posts): array
    {
        return array_map(function (\WP_Post $post): \WP_Post {
            $ad = $this->abTest->applyVariant([
                'id' => $post->ID,
                'title' => $post->post_title,
                'description' => $post->post_excerpt,
                'imageUrl' => get_the_post_thumbnail_url($post->ID) ?: null,
                'targetUrl' => get_permalink($post->ID) ?: null,
            ]);

            $post->post_title = (string) ($ad['title'] ?? $post->post_title);
            $post->post_excerpt = (string) ($ad['description'] ?? $post->post_excerpt);
            $post->imageUrl = $ad['imageUrl'] ?? null;
            $post->targetUrl = $ad['targetUrl'] ?? null;
            $post->abTest = $ad['abTest'] ?? null;

            return $post;
        }, $posts);
    }

    private function sidebarScore(int $postId): int
    {
        $plan = (string) get_post_meta($postId, 'ad_plan', true);
        $isFeatured = get_post_meta($postId, 'is_featured', true) === '1';
        $priorityScore = (int) get_post_meta($postId, 'priority_score', true);

        $impressions = (int) get_post_meta($postId, 'impression_count', true);
        $clicks = (int) get_post_meta($postId, 'click_count', true);
        $bid = (float) get_post_meta($postId, 'bid_amount', true);

        $ctr = $impressions > 0 ? ($clicks / $impressions) : 0.0;

        $planScore = match ($plan) {
            'premium', 'premium_monthly' => 1000,
            'featured', 'featured_monthly' => 600,
            default => $isFeatured ? 300 : 0,
        };

        return $planScore
            + $priorityScore
            + (int) round($bid * 100)
            + min(300, (int) round($ctr * 1000));
    }

    private function isVisible(int $postId): bool
    {
        $isActive = get_post_meta($postId, 'is_active', true);
        $status = (string) get_post_meta($postId, 'moderation_status', true);

        if ($isActive !== '' && $isActive === '0') {
            return false;
        }

        if ($status !== '' && !in_array($status, ['approved', 'publish'], true)) {
            return false;
        }

        return $this->isActiveAd($postId);
    }

    private function isPromoted(int $postId): bool
    {
        return $this->adPriority($postId) > 0;
    }

    private function isActiveAd(int $postId): bool
    {
        $startsAt = (string) get_post_meta($postId, 'ad_starts_at', true);
        $endsAt = (string) get_post_meta($postId, 'ad_ends_at', true);
        $expiresAt = (string) get_post_meta($postId, 'expires_at', true);
        $now = current_time('timestamp');

        if ($startsAt !== '') {
            $startsTimestamp = strtotime($startsAt);
            if ($startsTimestamp !== false && $startsTimestamp > $now) {
                return false;
            }
        }

        $effectiveEndsAt = $endsAt !== '' ? $endsAt : $expiresAt;

        if ($effectiveEndsAt !== '') {
            $endsTimestamp = strtotime($effectiveEndsAt);
            if ($endsTimestamp !== false && $endsTimestamp < $now) {
                return false;
            }
        }

        return true;
    }

    private function adPriority(int $postId): int
    {
        if (!$this->isActiveAd($postId)) {
            return 0;
        }

        $plan = (string) get_post_meta($postId, 'ad_plan', true);
        $isPaid = get_post_meta($postId, 'is_paid', true) === '1';
        $isFeatured = get_post_meta($postId, 'is_featured', true) === '1';
        $priorityScore = (int) get_post_meta($postId, 'priority_score', true);

        $impressions = (int) get_post_meta($postId, 'impression_count', true);
        $clicks = (int) get_post_meta($postId, 'click_count', true);

        $ctr = $impressions > 0 ? ($clicks / $impressions) : 0.0;

        $planPriority = match ($plan) {
            'premium' => 300,
            'featured' => 200,
            default => $isPaid || $isFeatured ? 100 : 0,
        };

        $ctrScore = min(100, (int) round($ctr * 500));

        return $planPriority + $priorityScore + $ctrScore;
    }

    public function sidebarMultiple(array $filters = [], int $limit = 3): array
    {
        $grouped = $this->list(array_merge($filters, [
            'placement' => 'sidebar_right',
        ]));

        $items = array_values(array_merge($grouped['featured'], $grouped['standard']));

        if ($items === []) {
            return [];
        }

        // 🔥 premium 우선 풀
        $premium = array_filter($items, function (\WP_Post $post) {
            $plan = (string) get_post_meta($post->ID, 'ad_plan', true);
            return in_array($plan, ['premium', 'premium_monthly'], true);
        });

        $pool = $premium !== [] ? array_values($premium) : $items;

        // 🔥 성과 기반 정렬
        usort($pool, function (\WP_Post $a, \WP_Post $b): int {
            return $this->sidebarScore($b->ID) <=> $this->sidebarScore($a->ID);
        });

        // 🔥 상위 N개
        $selected = array_slice($pool, 0, $limit);

        return $selected;
    }
}