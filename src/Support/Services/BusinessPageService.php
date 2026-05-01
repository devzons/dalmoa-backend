<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Services;

use DalmoaCore\Query\BusinessPageQuery;

final class BusinessPageService
{
    public function __construct(
        private readonly BusinessPageQuery $query = new BusinessPageQuery(),
    ) {}

    /**
     * @param array{
     *   q?: ?string,
     *   category?: ?string,
     *   featured?: bool
     * } $filters
     *
     * @return \WP_Post[]
     */
    public function list(array $filters = []): array
    {
        $posts = $this->query->list($filters);

        return array_values(array_filter($posts, function (\WP_Post $post): bool {
            return $this->isVisible($post->ID);
        }));
    }

    public function findBySlug(string $slug): ?\WP_Post
    {
        $post = $this->query->findBySlug($slug);

        if (!$post instanceof \WP_Post) {
            return null;
        }

        if (!$this->isVisible($post->ID)) {
            return null;
        }

        return $post;
    }

    /**
     * @return \WP_Post[]
     */
    public function search(string $q): array
    {
        return $this->list(['q' => $q]);
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