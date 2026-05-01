<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class RealEstatePostType
{
    public static function register(): void
    {
        register_post_type('real_estate', [
            'labels' => [
                'name' => 'Real Estate',
                'singular_name' => 'Real Estate',
                'add_new_item' => 'Add New Property',
                'edit_item' => 'Edit Property',
                'all_items' => 'All Properties',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-building',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'real-estate'],
            'has_archive' => false,
            'taxonomies' => ['real_estate_category'],
        ]);

        register_taxonomy('real_estate_category', ['real_estate'], [
            'labels' => [
                'name' => 'Property Categories',
                'singular_name' => 'Property Category',
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
            'rewrite' => ['slug' => 'real-estate-category'],
        ]);
    }
}