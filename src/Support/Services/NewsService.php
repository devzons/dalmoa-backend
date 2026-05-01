<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Services;

use DalmoaCore\Query\NewsQuery;

final class NewsService
{
    use SortsFeaturedListings;

    public function __construct(
        private readonly NewsQuery $query = new NewsQuery(),
    ) {}

    public function list(array $filters = []): array
    {
        $page = isset($filters['page']) && is_numeric($filters['page'])
            ? max(1, (int) $filters['page'])
            : 1;

        $perPage = isset($filters['perPage']) && is_numeric($filters['perPage'])
            ? min(30, max(1, (int) $filters['perPage']))
            : 12;

        $posts = $this->query->list($filters);

        $visiblePosts = array_values(array_filter($posts, function (\WP_Post $post): bool {
            return $this->isVisible($post->ID);
        }));

        $sortedPosts = $this->sortByFeaturedPriority($visiblePosts);
        $total = count($sortedPosts);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        return [
            'items' => array_slice($sortedPosts, $offset, $perPage),
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

    private function isVisible(int $postId): bool
    {
        $isActive = get_post_meta($postId, 'is_active', true);
        $status = (string) get_post_meta($postId, 'moderation_status', true);

        if ($isActive === '0') {
            return false;
        }

        if ($status !== '' && !in_array($status, ['approved', 'publish', ''], true)) {
            return false;
        }

        return true;
    }
}