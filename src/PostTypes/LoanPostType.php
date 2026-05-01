<?php
declare(strict_types=1);

namespace DalmoaCore\PostTypes;

final class LoanPostType
{
    public static function register(): void
    {
        register_post_type('loan', [
            'labels' => [
                'name' => 'Loans',
                'singular_name' => 'Loan',
                'add_new_item' => 'Add New Loan',
                'edit_item' => 'Edit Loan',
                'all_items' => 'All Loans',
            ],
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-money-alt',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
            'rewrite' => ['slug' => 'loan'],
            'has_archive' => false,
            'taxonomies' => ['loan_category'],
        ]);

        register_taxonomy('loan_category', ['loan'], [
            'labels' => [
                'name' => 'Loan Categories',
                'singular_name' => 'Loan Category',
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
            'rewrite' => ['slug' => 'loan-category'],
        ]);
    }
}