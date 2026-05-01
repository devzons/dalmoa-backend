<?php
declare(strict_types=1);

namespace DalmoaCore\Query;

final class DirectoryQuery
{
    public function list(array $filters = []): array
    {
        $metaQuery = ['relation' => 'AND'];

        $category = isset($filters['category']) ? trim((string) $filters['category']) : '';
        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        $featured = !empty($filters['featured']);
        $sort = isset($filters['sort']) ? sanitize_key((string) $filters['sort']) : '';

        if ($category !== '') {
            $metaQuery[] = [
                'key' => 'business_category',
                'value' => sanitize_key($category),
                'compare' => '=',
            ];
        }

        if ($featured) {
            $metaQuery[] = [
                'key' => 'is_featured',
                'value' => '1',
                'compare' => '=',
            ];
        }

        $args = [
            'post_type' => 'directory',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if ($sort === 'popular') {
            $args['meta_key'] = 'view_count';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
        } else {
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
        }

        if ($q !== '') {
            $args['s'] = $q;
        }

        if (count($metaQuery) > 1) {
            $args['meta_query'] = $metaQuery;
        }

        if ($q !== '') {
            add_filter('posts_search', [$this, 'expandSearchToMeta'], 10, 2);
            $posts = get_posts($args);
            remove_filter('posts_search', [$this, 'expandSearchToMeta'], 10);

            return $this->sortFeaturedFirst($posts);
        }

        return $this->sortFeaturedFirst(get_posts($args));
    }

    private function sortFeaturedFirst(array $posts): array
    {
        usort($posts, function (\WP_Post $a, \WP_Post $b): int {
            $aFeatured = (int) get_post_meta($a->ID, 'is_featured', true);
            $bFeatured = (int) get_post_meta($b->ID, 'is_featured', true);

            if ($aFeatured !== $bFeatured) {
                return $bFeatured <=> $aFeatured;
            }

            return strtotime($b->post_date) <=> strtotime($a->post_date);
        });

        return $posts;
    }

    public function expandSearchToMeta(string $search, \WP_Query $query): string
    {
        global $wpdb;

        $q = $query->get('s');

        if (!is_string($q) || trim($q) === '') {
            return $search;
        }

        $like = '%' . $wpdb->esc_like(trim($q)) . '%';

        return $wpdb->prepare(
            " AND (
                {$wpdb->posts}.post_title LIKE %s
                OR {$wpdb->posts}.post_excerpt LIKE %s
                OR {$wpdb->posts}.post_content LIKE %s
                OR EXISTS (
                    SELECT 1 FROM {$wpdb->postmeta}
                    WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
                    AND {$wpdb->postmeta}.meta_value LIKE %s
                )
            ) ",
            $like,
            $like,
            $like,
            $like
        );
    }

    public function findBySlug(string $slug): ?\WP_Post
    {
        $posts = get_posts([
            'post_type' => 'directory',
            'post_status' => 'publish',
            'name' => sanitize_title(urldecode($slug)),
            'posts_per_page' => 1,
        ]);

        return $posts[0] ?? null;
    }
}