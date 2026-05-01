<?php
declare(strict_types=1);

namespace DalmoaCore\Admin\MetaBoxes;

use DalmoaCore\Ads\AbTesting\Support\AdAbTestMetaKeys;

final class AdAbTestMetaBox
{
    public function register(): void
    {
        add_action('add_meta_boxes', function (): void {
            add_meta_box(
                'dalmoa_ad_ab_test',
                'A/B Test',
                [$this, 'render'],
                'ad_listing',
                'normal',
                'default'
            );
        });

        add_action('save_post_ad_listing', [$this, 'save']);
    }

    public function render(\WP_Post $post): void
    {
        $enabled = get_post_meta($post->ID, AdAbTestMetaKeys::ENABLED, true);
        $strategy = get_post_meta($post->ID, AdAbTestMetaKeys::STRATEGY, true) ?: AdAbTestMetaKeys::STRATEGY_WEIGHTED;
        $variants = get_post_meta($post->ID, AdAbTestMetaKeys::VARIANTS, true);

        ?>
        <p>
            <label>
                <input type="checkbox" name="ab_enabled" value="1" <?php checked($enabled, '1'); ?> />
                A/B 테스트 활성화
            </label>
        </p>

        <p>
            <label>전략</label><br/>
            <select name="ab_strategy">
                <option value="weighted" <?php selected($strategy, 'weighted'); ?>>Weighted</option>
                <option value="auto_ctr" <?php selected($strategy, 'auto_ctr'); ?>>Auto CTR</option>
            </select>
        </p>

        <p>
            <label>Variants (JSON)</label>
            <textarea name="ab_variants" style="width:100%;height:200px;"><?php echo esc_textarea(is_string($variants) ? $variants : ''); ?></textarea>
        </p>
        <?php
    }

    public function save(int $postId): void
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        update_post_meta(
            $postId,
            AdAbTestMetaKeys::ENABLED,
            isset($_POST['ab_enabled']) ? '1' : '0'
        );

        if (isset($_POST['ab_strategy'])) {
            update_post_meta(
                $postId,
                AdAbTestMetaKeys::STRATEGY,
                sanitize_key($_POST['ab_strategy'])
            );
        }

        if (isset($_POST['ab_variants'])) {
            update_post_meta(
                $postId,
                AdAbTestMetaKeys::VARIANTS,
                wp_kses_post($_POST['ab_variants'])
            );
        }
    }
}