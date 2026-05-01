<?php
declare(strict_types=1);

namespace DalmoaCore\Query;

final class TownBoardQuery
{
    public function list(array $filters = []): array
    {
        $metaQuery = ['relation' => 'AND'];
        $taxQuery = ['relation' => 'AND'];

        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        $category = isset($filters['category']) ? trim((string) $filters['category']) : '';
        $featured = !empty($filters['featured']);

        if ($category !== '') {
            $taxQuery[] = [
                'taxonomy' => 'town_board_category',
                'field' => 'slug',
                'terms' => [$category],
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
            'post_type' => 'town_board',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if ($q !== '') {
            $args['s'] = $q;
        }

        if (count($metaQuery) > 1) {
            $args['meta_query'] = $metaQuery;
        }

        if (count($taxQuery) > 1) {
            $args['tax_query'] = $taxQuery;
        }

        if ($q !== '') {
            add_filter('posts_search', [$this, 'expandSearchToMeta'], 10, 2);
            $posts = get_posts($args);
            remove_filter('posts_search', [$this, 'expandSearchToMeta'], 10);

            return $posts;
        }

        return get_posts($args);
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
            'post_type' => 'town_board',
            'post_status' => 'publish',
            'name' => sanitize_title($slug),
            'posts_per_page' => 1,
        ]);

        return $posts[0] ?? null;
    }
}