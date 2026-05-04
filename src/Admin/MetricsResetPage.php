<?php
declare(strict_types=1);

namespace DalmoaCore\Admin;

use WP_Query;

final class MetricsResetPage
{
    public function init(): void
    {
        add_action('admin_menu', [$this, 'registerPage']);
        add_action('admin_post_dalmoa_reset_metrics', [$this, 'handleReset']);
    }

    public function registerPage(): void
    {
        add_management_page(
            'Dalmoa Metrics Reset',
            'Dalmoa Metrics Reset',
            'manage_options',
            'dalmoa-metrics-reset',
            [$this, 'render']
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('권한이 없습니다.');
        }

        $success = isset($_GET['success']);
        ?>
        <div class="wrap">
            <h1>Dalmoa Metrics Reset</h1>

            <?php if ($success): ?>
                <div class="notice notice-success is-dismissible">
                    <p>조회수/클릭수 리셋 완료</p>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('dalmoa_reset_metrics'); ?>
                <input type="hidden" name="action" value="dalmoa_reset_metrics">
                <?php submit_button('전체 조회수 / 클릭수 리셋', 'delete'); ?>
            </form>
        </div>
        <?php
    }

    public function handleReset(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('권한이 없습니다.');
        }

        check_admin_referer('dalmoa_reset_metrics');

        global $wpdb;

        $postTypes = [
            'directory',
            'job',
            'marketplace',
            'real_estate',
            'car',
            'business_sale',
            'loan',
            'town_board',
            'news',
            'ad_listing',
        ];

        $metaKeys = [
            'viewCount',
            'view_count',
            'views',
            'hitCount',
            '_view_count',
            'clickCount',
            'click_count',
            'clicks',
            '_click_count',
            'impressionCount',
            'impression_count',
            'impressions',
            '_impression_count',
        ];

        $query = new WP_Query([
            'post_type' => $postTypes,
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);

        foreach ($query->posts as $postId) {
            foreach ($metaKeys as $key) {
                update_post_meta((int) $postId, $key, 0);
            }

            clean_post_cache((int) $postId);
        }

        $placeholders = implode(',', array_fill(0, count($metaKeys), '%s'));

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->postmeta}
                 SET meta_value = '0'
                 WHERE meta_key IN ($placeholders)",
                ...$metaKeys
            )
        );

        wp_cache_flush();

        wp_safe_redirect(admin_url('tools.php?page=dalmoa-metrics-reset&success=1'));
        exit;
    }
}