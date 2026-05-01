<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class CarPostType
{
    public static function register(): void
    {
        register_post_type('car', [
            'labels' => [
                'name' => 'Cars',
                'singular_name' => 'Car',
                'add_new_item' => 'Add New Car',
                'edit_item' => 'Edit Car',
                'all_items' => 'All Cars',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-car',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'cars'],
            'has_archive' => false,
            'taxonomies' => ['car_category'],
        ]);

        register_taxonomy('car_category', ['car'], [
            'labels' => [
                'name' => 'Car Categories',
                'singular_name' => 'Car Category',
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
            'rewrite' => ['slug' => 'car-category'],
        ]);
    }
}