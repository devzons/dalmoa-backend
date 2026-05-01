<?php
/**
 * Plugin Name: Dalmoa Core
 * Plugin URI: https://dalmoa.com
 * Description: Core plugin for Dalmoa Hub - CPTs, fields, admin UX, REST API, and shared business logic.
 * Version: 1.0.0
 * Author: Dalmoa
 * Author URI: https://dalmoa.com
 * Text Domain: dalmoa-core
 * Requires at least: 6.5
 * Requires PHP: 8.2
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('DALMOA_CORE_VERSION', '1.0.0');
define('DALMOA_CORE_FILE', __FILE__);
define('DALMOA_CORE_PATH', plugin_dir_path(__FILE__));
define('DALMOA_CORE_URL', plugin_dir_url(__FILE__));

/**
 * Composer autoload
 */
$autoload = DALMOA_CORE_PATH . 'vendor/autoload.php';

if (file_exists($autoload)) {
    require_once $autoload;
} else {
    add_action('admin_notices', static function (): void {
        if (!current_user_can('manage_options')) {
            return;
        }

        echo '<div class="notice notice-error"><p><strong>Dalmoa Core:</strong> vendor/autoload.php 파일이 없습니다. 플러그인 폴더에서 <code>composer install</code> 또는 <code>composer dump-autoload</code>를 실행하세요.</p></div>';
    });

    return;
}

/**
 * Boot plugin
 */
add_action('plugins_loaded', static function (): void {
    if (!class_exists(\DalmoaCore\Bootstrap::class)) {
        add_action('admin_notices', static function (): void {
            if (!current_user_can('manage_options')) {
                return;
            }

            echo '<div class="notice notice-error"><p><strong>Dalmoa Core:</strong> Bootstrap 클래스를 찾을 수 없습니다.</p></div>';
        });

        return;
    }

    (new \DalmoaCore\Bootstrap())->init();
});

/**
 * Activation
 */
register_activation_hook(__FILE__, static function (): void {
    if (class_exists(\DalmoaCore\PostTypes\DirectoryPostType::class)) {
        \DalmoaCore\PostTypes\DirectoryPostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\AdListingPostType::class)) {
        \DalmoaCore\PostTypes\AdListingPostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\BusinessPagePostType::class)) {
        \DalmoaCore\PostTypes\BusinessPagePostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\BusinessSalePostType::class)) {
        \DalmoaCore\PostTypes\BusinessSalePostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\NewsPostType::class)) {
        \DalmoaCore\PostTypes\NewsPostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\JobPostType::class)) {
        \DalmoaCore\PostTypes\JobPostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\LoanPostType::class)) {
        \DalmoaCore\PostTypes\LoanPostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\MarketplacePostType::class)) {
        \DalmoaCore\PostTypes\MarketplacePostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\RealEstatePostType::class)) {
        \DalmoaCore\PostTypes\RealEstatePostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\CarPostType::class)) {
        \DalmoaCore\PostTypes\CarPostType::register();
    }

    if (class_exists(\DalmoaCore\PostTypes\TownBoardPostType::class)) {
        \DalmoaCore\PostTypes\TownBoardPostType::register();
    }

    flush_rewrite_rules();
});

/**
 * Deactivation
 */
register_deactivation_hook(__FILE__, static function (): void {
    flush_rewrite_rules();
});