<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Services;

use DalmoaCore\Query\DirectoryQuery;

final class DirectoryService
{
    use SortsFeaturedListings;

    public function __construct(
        private readonly DirectoryQuery $query = new DirectoryQuery(),
    ) {}

    public function list(array $filters = []): array
    {
        $page = isset($filters['page']) && is_numeric($filters['page'])
            ? max(1, (int) $filters['page'])
            : 1;

        $perPage = isset($filters['perPage']) && is_numeric($filters['perPage'])
            ? min(50, max(1, (int) $filters['perPage']))
            : 30;

        $posts = $this->query->list($filters);

        $visiblePosts = array_values(array_filter($posts, function (\WP_Post $post): bool {
            return $this->isVisible($post->ID);
        }));

        $sortedPosts = $this->sortByFeaturedPriority($visiblePosts);

        $promotedPosts = array_values(array_filter($sortedPosts, function (\WP_Post $post): bool {
            return $this->isPromoted($post->ID);
        }));

        $regularPosts = array_values(array_filter($sortedPosts, function (\WP_Post $post): bool {
            return !$this->isPromoted($post->ID);
        }));

        $total = count($regularPosts);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        $items = array_slice($regularPosts, $offset, $perPage);

        if ($page === 1) {
            $items = array_merge($promotedPosts, $items);
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages,
        ];
    }

    public function findBySlug(string $slug): ?\WP_Post
    {
        $post = $this->query->findBySlug($slug);

        if (!$post instanceof \WP_Post) {
            return null;
        }

        return $this->isVisible($post->ID) ? $post : null;
    }

    public function incrementViews(int $postId): void
    {
        $views = (int) get_post_meta($postId, 'view_count', true);

        update_post_meta($postId, 'view_count', (string) ($views + 1));
    }

    private function isPromoted(int $postId): bool
    {
        $adPlan = (string) get_post_meta($postId, 'ad_plan', true);
        $isPaid = (string) get_post_meta($postId, 'is_paid', true);
        $isFeatured = (string) get_post_meta($postId, 'is_featured', true);

        return in_array($adPlan, ['premium', 'featured'], true)
            || $isPaid === '1'
            || $isFeatured === '1';
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

        return true;
    }
}