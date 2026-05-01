<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class NewsPostType
{
    public static function register(): void
    {
        register_post_type('news', [
            'labels' => [
                'name' => 'News',
                'singular_name' => 'News',
                'add_new_item' => 'Add News',
                'edit_item' => 'Edit News',
                'all_items' => 'All News',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-media-document',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'news'],
            'has_archive' => false,
            'taxonomies' => ['news_category'],
        ]);

        register_taxonomy('news_category', ['news'], [
            'labels' => [
                'name' => 'News Categories',
                'singular_name' => 'News Category',
                'menu_name' => 'Categories',
                'all_items' => 'All Categories',
                'edit_item' => 'Edit Category',
                'add_new_item' => 'Add New Category',
            ],
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'hierarchical' => true,
            'rewrite' => ['slug' => 'news-category'],
        ]);
    }
}