<?php
declare(strict_types=1);

namespace DalmoaCore\Admin\ListTable;

final class AdminColumns
{
    /**
     * @param string[] $postTypes
     */
    public function register(array $postTypes): void
    {
        foreach ($postTypes as $postType) {
            add_filter("manage_{$postType}_posts_columns", function (array $columns) use ($postType) {
                $new = [];

                foreach ($columns as $key => $label) {
                    if ($key === 'title') {
                        $new['thumbnail'] = '썸네일';
                    }

                    $new[$key] = $label;

                    if ($key === 'title') {
                        $new['is_featured'] = '추천';

                        if ($postType === 'ad_listing') {
                            $new['ad_plan'] = '광고상품';
                            $new['ad_ends_at'] = '만료일';
                            $new['ad_stats'] = '성과';
                        }

                        $new['is_active'] = '활성';
                        $new['moderation_status'] = '검수상태';
                    }
                }

                return $new;
            });

            add_action("manage_{$postType}_posts_custom_column", function (string $column, int $postId) {
                switch ($column) {
                    case 'thumbnail':
                        $thumbnailId = (int) get_post_meta($postId, 'thumbnail_id', true);
                        $image = $thumbnailId > 0 ? wp_get_attachment_image($thumbnailId, [60, 60]) : '';

                        echo $image ?: '—';
                        break;

                    case 'is_featured':
                        $isFeatured = get_post_meta($postId, 'is_featured', true) === '1';
                        echo AdminQuickActions::renderBooleanToggle($postId, 'is_featured', $isFeatured);
                        break;

                    case 'ad_plan':
                        $plan = (string) get_post_meta($postId, 'ad_plan', true);
                        $plan = $plan !== '' ? $plan : 'basic';

                        $label = match ($plan) {
                            'premium' => 'Premium',
                            'featured' => 'Featured',
                            default => 'Basic',
                        };

                        echo '<strong>' . esc_html($label) . '</strong>';
                        break;

                    case 'ad_ends_at':
                        $endsAt = (string) get_post_meta($postId, 'ad_ends_at', true);
                        $expiresAt = (string) get_post_meta($postId, 'expires_at', true);
                        $value = $endsAt !== '' ? $endsAt : $expiresAt;

                        if ($value === '') {
                            echo '—';
                            break;
                        }

                        $timestamp = strtotime($value);
                        $expired = $timestamp !== false && $timestamp < current_time('timestamp');

                        echo '<span style="color:' . ($expired ? '#dc2626' : '#16a34a') . ';font-weight:600;">';
                        echo esc_html($value);
                        echo $expired ? ' (만료)' : '';
                        echo '</span>';
                        break;

                    case 'ad_stats':
                        $clicks = (int) get_post_meta($postId, 'click_count', true);
                        $impressions = (int) get_post_meta($postId, 'impression_count', true);
                        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 1) : 0;

                        echo '<div style="font-size:12px;line-height:1.6;">';
                        echo '노출: <strong>' . esc_html((string) $impressions) . '</strong><br>';
                        echo '클릭: <strong>' . esc_html((string) $clicks) . '</strong><br>';
                        echo 'CTR: <strong>' . esc_html((string) $ctr) . '%</strong>';
                        echo '</div>';
                        break;

                    case 'is_active':
                        $isActive = get_post_meta($postId, 'is_active', true) !== '0';
                        echo AdminQuickActions::renderBooleanToggle($postId, 'is_active', $isActive);
                        break;

                    case 'moderation_status':
                        $status = (string) get_post_meta($postId, 'moderation_status', true);
                        $status = $status !== '' ? $status : 'approved';
                        echo AdminQuickActions::renderStatusButtons($postId, $status);
                        break;
                }
            }, 10, 2);
        }
    }
}