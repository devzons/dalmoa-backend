<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class MarketplacePostType
{
    public static function register(): void
    {
        register_post_type('marketplace', [
            'labels' => [
                'name' => 'Marketplace',
                'singular_name' => 'Marketplace',
                'add_new_item' => 'Add New Item',
                'edit_item' => 'Edit Item',
                'all_items' => 'All Items',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-cart',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'marketplace'],
            'has_archive' => false,
            'taxonomies' => ['marketplace_category'],
        ]);

        register_taxonomy('marketplace_category', ['marketplace'], [
            'labels' => [
                'name' => 'Marketplace Categories',
                'singular_name' => 'Marketplace Category',
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
            'rewrite' => ['slug' => 'marketplace-category'],
        ]);
    }
}