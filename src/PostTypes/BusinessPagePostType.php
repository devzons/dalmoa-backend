<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class BusinessPagePostType
{
    public static function register(): void
    {
        register_post_type('business_page', [
            'labels' => [
                'name' => 'Business Pages',
                'singular_name' => 'Business Page',
                'add_new_item' => 'Add New Business Page',
                'edit_item' => 'Edit Business Page',
                'all_items' => 'All Business Pages',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-layout',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'business'],
            'has_archive' => false,
            'taxonomies' => ['business_page_category'],
        ]);

        register_taxonomy('business_page_category', ['business_page'], [
            'labels' => [
                'name' => 'Business Categories',
                'singular_name' => 'Business Category',
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
            'rewrite' => ['slug' => 'business-category'],
        ]);
    }
}