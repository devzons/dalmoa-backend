<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Services;

use DalmoaCore\Query\CarQuery;

final class CarService
{
    use SortsFeaturedListings;

    public function __construct(
        private readonly CarQuery $query = new CarQuery(),
    ) {}

    public function list(array $filters = []): array
    {
        $posts = $this->query->list($filters);

        $visiblePosts = array_values(array_filter($posts, function (\WP_Post $post) use ($filters): bool {
            if (!$this->isVisible($post->ID)) {
                return false;
            }

            if (!$this->matchesRegion($post->ID, $filters['region'] ?? null)) {
                return false;
            }

            if (!$this->matchesPriceRange(
                $post->ID,
                $filters['price_min'] ?? null,
                $filters['price_max'] ?? null
            )) {
                return false;
            }

            return true;
        }));

        $visiblePosts = $this->sortByFeaturedPriority($visiblePosts);

        $page = isset($filters['page']) && (int) $filters['page'] > 0 ? (int) $filters['page'] : 1;
        $perPage = (int) ($filters['perPage'] ?? 20);
        $total = count($visiblePosts);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $offset = ($page - 1) * $perPage;

        return [
            'items' => array_slice($visiblePosts, $offset, $perPage),
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

    private function matchesRegion(int $postId, ?string $region): bool
    {
        if ($region === null || $region === '') {
            return true;
        }

        $postRegion = (string) get_post_meta($postId, 'region', true);

        if ($postRegion === '') {
            return false;
        }

        return stripos($postRegion, $region) !== false;
    }

    private function matchesPriceRange(
        int $postId,
        ?int $priceMin,
        ?int $priceMax
    ): bool {
        if ($priceMin === null && $priceMax === null) {
            return true;
        }

        $rawPrice = get_post_meta($postId, 'price', true);
        $price = $this->normalizeNumber($rawPrice);

        if ($price === null) {
            return false;
        }

        if ($priceMin !== null && $price < $priceMin) {
            return false;
        }

        if ($priceMax !== null && $price > $priceMax) {
            return false;
        }

        return true;
    }

    private function normalizeNumber(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^\d]/', '', (string) $value);

        if ($normalized === '' || $normalized === null) {
            return null;
        }

        return (int) $normalized;
    }
}