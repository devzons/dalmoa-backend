<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class TownBoardPostType
{
    public static function register(): void
    {
        register_post_type('town_board', [
            'labels' => [
                'name' => 'Town Board',
                'singular_name' => 'Town Board',
                'add_new_item' => 'Add New Post',
                'edit_item' => 'Edit Post',
                'all_items' => 'All Posts',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-megaphone',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'town-board'],
            'has_archive' => false,
            'taxonomies' => ['town_board_category'],
        ]);

        register_taxonomy('town_board_category', ['town_board'], [
            'labels' => [
                'name' => 'Board Categories',
                'singular_name' => 'Board Category',
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
            'rewrite' => ['slug' => 'board-category'],
        ]);
    }
}