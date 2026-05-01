<?php
declare(strict_types=1);

namespace DalmoaCore\Query;

final class AdQuery
{
    public function listActive(array $filters = []): array
    {
        $metaQuery = ['relation' => 'AND'];
        $taxQuery = ['relation' => 'AND'];

        $q = isset($filters['q']) ? trim((string) $filters['q']) : '';
        $category = isset($filters['category']) ? trim((string) $filters['category']) : '';
        $now = current_time('mysql');

        $metaQuery[] = [
            'key' => 'is_active',
            'value' => '1',
            'compare' => '=',
        ];

        $metaQuery[] = [
            'relation' => 'OR',
            [
                'key' => 'payment_status',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => 'payment_status',
                'value' => ['expired', 'cancelled', 'canceled', 'payment_failed', 'unpaid', 'incomplete_expired'],
                'compare' => 'NOT IN',
            ],
        ];

        $metaQuery[] = [
            'relation' => 'OR',
            [
                'key' => 'subscription_status',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => 'subscription_status',
                'value' => ['canceled', 'cancelled', 'unpaid', 'incomplete_expired', 'paused'],
                'compare' => 'NOT IN',
            ],
        ];

        $metaQuery[] = [
            'relation' => 'OR',
            [
                'key' => 'ad_starts_at',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => 'ad_starts_at',
                'value' => '',
                'compare' => '=',
            ],
            [
                'key' => 'ad_starts_at',
                'value' => $now,
                'compare' => '<=',
                'type' => 'DATETIME',
            ],
        ];

        $metaQuery[] = [
            'relation' => 'OR',
            [
                'key' => 'ad_ends_at',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => 'ad_ends_at',
                'value' => '',
                'compare' => '=',
            ],
            [
                'key' => 'ad_ends_at',
                'value' => $now,
                'compare' => '>=',
                'type' => 'DATETIME',
            ],
        ];

        if ($category !== '') {
            $taxQuery[] = [
                'taxonomy' => 'ad_category',
                'field' => 'slug',
                'terms' => [$category],
            ];
        }

        if ($q !== '') {
            $metaQuery[] = [
                'relation' => 'OR',
                ['key' => 'title_ko', 'value' => $q, 'compare' => 'LIKE'],
                ['key' => 'title_en', 'value' => $q, 'compare' => 'LIKE'],
                ['key' => 'excerpt_ko', 'value' => $q, 'compare' => 'LIKE'],
                ['key' => 'excerpt_en', 'value' => $q, 'compare' => 'LIKE'],
                ['key' => 'region_key', 'value' => $q, 'compare' => 'LIKE'],
                ['key' => 'cta_label_ko', 'value' => $q, 'compare' => 'LIKE'],
                ['key' => 'cta_label_en', 'value' => $q, 'compare' => 'LIKE'],
            ];
        }

        $args = [
            'post_type' => 'ad_listing',
            'post_status' => 'publish',
            'posts_per_page' => 100,
            'meta_query' => $metaQuery,
            'meta_key' => 'priority_score',
            'orderby' => [
                'meta_value_num' => 'DESC',
                'date' => 'DESC',
            ],
            'no_found_rows' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ];

        if (count($taxQuery) > 1) {
            $args['tax_query'] = $taxQuery;
        }

        return get_posts($args);
    }

    public function findBySlug(string $slug): ?\WP_Post
    {
        $posts = get_posts([
            'post_type' => 'ad_listing',
            'post_status' => 'publish',
            'name' => sanitize_title(urldecode($slug)),
            'posts_per_page' => 1,
            'no_found_rows' => true,
            'update_post_meta_cache' => true,
            'update_post_term_cache' => false,
        ]);

        return $posts[0] ?? null;
    }
}