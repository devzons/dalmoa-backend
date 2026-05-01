<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class BusinessSalePostType
{
    public static function register(): void
    {
        register_post_type('business_sale', [
            'labels' => [
                'name' => 'Business Sales',
                'singular_name' => 'Business Sale',
                'add_new_item' => 'Add New Business Sale',
                'edit_item' => 'Edit Business Sale',
                'all_items' => 'All Business Sales',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-store',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'business-sale'],
            'has_archive' => false,
            'taxonomies' => ['business_sale_category'],
        ]);

        register_taxonomy('business_sale_category', ['business_sale'], [
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
            'rewrite' => ['slug' => 'business-sale-category'],
        ]);
    }
}