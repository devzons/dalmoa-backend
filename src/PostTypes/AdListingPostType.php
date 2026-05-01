<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class AdListingPostType
{
    public static function register(): void
    {
        register_post_type('ad_listing', [
            'labels' => [
                'name' => 'Ads',
                'singular_name' => 'Ad',
                'add_new_item' => 'Add New Ad',
                'edit_item' => 'Edit Ad',
                'all_items' => 'All Ads',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-megaphone',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'ads'],
            'has_archive' => false,
            'taxonomies' => ['ad_category'],
        ]);

        register_taxonomy('ad_category', ['ad_listing'], [
            'labels' => [
                'name' => 'Ad Categories',
                'singular_name' => 'Ad Category',
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
            'rewrite' => ['slug' => 'ad-category'],
        ]);
    }
}