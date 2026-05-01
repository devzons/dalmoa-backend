<?php
declare(strict_types=1);

namespace DalmoaCore\Admin;

final class AdPlanMetaBox
{
    private const POST_TYPES = [
        'ad_listing',
        'directory',
        'business_sale',
        'job',
        'loan',
        'marketplace',
        'real_estate',
        'car',
        'town_board',
        'news',
    ];

    public function register(): void
    {
        add_action('add_meta_boxes', [$this, 'addMetaBox']);
        add_action('save_post', [$this, 'save'], 10, 2);
    }

    public function addMetaBox(): void
    {
        foreach (self::POST_TYPES as $postType) {
            add_meta_box(
                'dalmoa_ad_plan',
                'Dalmoa 광고 설정',
                [$this, 'render'],
                $postType,
                'side',
                'high'
            );
        }
    }

    public function render(\WP_Post $post): void
    {
        wp_nonce_field('dalmoa_save_ad_plan', 'dalmoa_ad_plan_nonce');

        $adPlan = (string) get_post_meta($post->ID, 'ad_plan', true);
        $isPaid = (string) get_post_meta($post->ID, 'is_paid', true);
        $isFeatured = (string) get_post_meta($post->ID, 'is_featured', true);
        $priorityScore = (string) get_post_meta($post->ID, 'priority_score', true);
        $adStartsAt = (string) get_post_meta($post->ID, 'ad_starts_at', true);
        $adEndsAt = (string) get_post_meta($post->ID, 'ad_ends_at', true);

        ?>
        <p>
            <label for="dalmoa_ad_plan"><strong>광고 플랜</strong></label>
            <select name="dalmoa_ad_plan" id="dalmoa_ad_plan" style="width:100%;">
                <option value="" <?php selected($adPlan, ''); ?>>없음</option>
                <option value="basic" <?php selected($adPlan, 'basic'); ?>>Basic</option>
                <option value="featured" <?php selected($adPlan, 'featured'); ?>>Featured</option>
                <option value="premium" <?php selected($adPlan, 'premium'); ?>>Premium</option>
            </select>
        </p>

        <p>
            <label>
                <input type="checkbox" name="dalmoa_is_paid" value="1" <?php checked($isPaid, '1'); ?>>
                유료 광고
            </label>
        </p>

        <p>
            <label>
                <input type="checkbox" name="dalmoa_is_featured" value="1" <?php checked($isFeatured, '1'); ?>>
                상단 카드 노출
            </label>
        </p>

        <p>
            <label for="dalmoa_priority_score"><strong>우선순위 점수</strong></label>
            <input
                type="number"
                name="dalmoa_priority_score"
                id="dalmoa_priority_score"
                value="<?php echo esc_attr($priorityScore); ?>"
                style="width:100%;"
                placeholder="premium 300 / featured 200"
            >
        </p>

        <p>
            <label for="dalmoa_ad_starts_at"><strong>광고 시작일</strong></label>
            <input
                type="datetime-local"
                name="dalmoa_ad_starts_at"
                id="dalmoa_ad_starts_at"
                value="<?php echo esc_attr($this->toDatetimeLocal($adStartsAt)); ?>"
                style="width:100%;"
            >
        </p>

        <p>
            <label for="dalmoa_ad_ends_at"><strong>광고 종료일</strong></label>
            <input
                type="datetime-local"
                name="dalmoa_ad_ends_at"
                id="dalmoa_ad_ends_at"
                value="<?php echo esc_attr($this->toDatetimeLocal($adEndsAt)); ?>"
                style="width:100%;"
            >
        </p>
        <?php
    }

    public function save(int $postId, \WP_Post $post): void
    {
        if (!in_array($post->post_type, self::POST_TYPES, true)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!isset($_POST['dalmoa_ad_plan_nonce']) || !wp_verify_nonce((string) $_POST['dalmoa_ad_plan_nonce'], 'dalmoa_save_ad_plan')) {
            return;
        }

        if (!current_user_can('edit_post', $postId)) {
            return;
        }

        $adPlan = sanitize_key((string) ($_POST['dalmoa_ad_plan'] ?? ''));
        $allowedPlans = ['', 'basic', 'featured', 'premium'];

        if (!in_array($adPlan, $allowedPlans, true)) {
            $adPlan = '';
        }

        $isPaid = isset($_POST['dalmoa_is_paid']) ? '1' : '0';
        $isFeatured = isset($_POST['dalmoa_is_featured']) ? '1' : '0';

        $priorityScore = isset($_POST['dalmoa_priority_score'])
            ? max(0, (int) $_POST['dalmoa_priority_score'])
            : 0;

        if ($priorityScore === 0) {
            $priorityScore = match ($adPlan) {
                'premium' => 300,
                'featured' => 200,
                'basic' => 100,
                default => 0,
            };
        }

        if ($adPlan === '') {
            delete_post_meta($postId, 'ad_plan');
            delete_post_meta($postId, 'purchased_plan');
            update_post_meta($postId, 'is_paid', '0');
            update_post_meta($postId, 'priority_score', '0');
        } else {
            update_post_meta($postId, 'ad_plan', $adPlan);
            update_post_meta($postId, 'purchased_plan', $adPlan);
            update_post_meta($postId, 'is_paid', $isPaid);
            update_post_meta($postId, 'priority_score', (string) $priorityScore);
        }

        update_post_meta($postId, 'is_featured', $isFeatured);

        $adStartsAt = $this->sanitizeDateTime((string) ($_POST['dalmoa_ad_starts_at'] ?? ''));
        $adEndsAt = $this->sanitizeDateTime((string) ($_POST['dalmoa_ad_ends_at'] ?? ''));

        if ($adStartsAt !== '') {
            update_post_meta($postId, 'ad_starts_at', $adStartsAt);
        } else {
            delete_post_meta($postId, 'ad_starts_at');
        }

        if ($adEndsAt !== '') {
            update_post_meta($postId, 'ad_ends_at', $adEndsAt);
            update_post_meta($postId, 'expires_at', $adEndsAt);
        } else {
            delete_post_meta($postId, 'ad_ends_at');
            delete_post_meta($postId, 'expires_at');
        }
    }

    private function sanitizeDateTime(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return '';
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function toDatetimeLocal(string $value): string
    {
        if ($value === '') {
            return '';
        }

        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return '';
        }

        return date('Y-m-d\TH:i', $timestamp);
    }
}