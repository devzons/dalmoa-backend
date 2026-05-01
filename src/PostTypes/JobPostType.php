<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class JobPostType
{
    public static function register(): void
    {
        register_post_type('job', [
            'labels' => [
                'name' => 'Jobs',
                'singular_name' => 'Job',
                'add_new_item' => 'Add New Job',
                'edit_item' => 'Edit Job',
                'all_items' => 'All Jobs',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-businessman',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'jobs'],
            'has_archive' => false,
            'taxonomies' => ['job_category'],
        ]);

        register_taxonomy('job_category', ['job'], [
            'labels' => [
                'name' => 'Job Categories',
                'singular_name' => 'Job Category',
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
            'rewrite' => ['slug' => 'job-category'],
        ]);
    }
}