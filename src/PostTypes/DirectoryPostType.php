<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class DirectoryPostType
{
    public static function register(): void
    {
        register_post_type('directory', [
            'labels' => [
                'name' => 'Directory',
                'singular_name' => 'Directory',
                'add_new_item' => 'Add New Directory',
                'edit_item' => 'Edit Directory',
                'all_items' => 'All Directory',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-store',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'directory'],
            'has_archive' => false,
            'taxonomies' => ['directory_category'],
        ]);

        register_taxonomy('directory_category', ['directory'], [
            'labels' => [
                'name' => 'Directory Categories',
                'singular_name' => 'Directory Category',
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
            'rewrite' => ['slug' => 'directory-category'],
        ]);
    }
}