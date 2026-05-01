<?php
declare(strict_types=1);

namespace DalmoaCore\Admin\ListTable;

use DalmoaCore\Support\Labels\DisplayLabel;

final class AdminQuickActions
{
    /**
     * @param string[] $postTypes
     */
    public function register(array $postTypes): void
    {
        add_action('admin_enqueue_scripts', function (string $hook) use ($postTypes): void {
            if (!in_array($hook, ['edit.php'], true)) {
                return;
            }

            $screen = get_current_screen();

            if (!$screen || !in_array((string) $screen->post_type, $postTypes, true)) {
                return;
            }

            wp_enqueue_script('jquery');

            wp_register_script(
                'dalmoa-admin-quick-actions',
                '',
                ['jquery'],
                '1.0.0',
                true
            );

            wp_enqueue_script('dalmoa-admin-quick-actions');

            wp_localize_script('dalmoa-admin-quick-actions', 'dalmoaAdminQuickActions', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('dalmoa_admin_quick_action'),
                'messages' => [
                    'error' => '처리 중 오류가 발생했습니다.',
                ],
            ]);

            wp_add_inline_script('dalmoa-admin-quick-actions', <<<'JS'
jQuery(function($) {
  $(document).on('click', '.dalmoa-quick-toggle, .dalmoa-quick-status', function(e) {
    e.preventDefault();

    const $button = $(this);
    const postId = $button.data('postId');
    const field = $button.data('field');
    const value = $button.data('value');

    if (!postId || !field) {
      return;
    }

    if ($button.data('loading') === true) {
      return;
    }

    $button.data('loading', true);
    $button.css('opacity', '0.6');

    $.post(dalmoaAdminQuickActions.ajaxUrl, {
      action: 'dalmoa_admin_quick_action',
      nonce: dalmoaAdminQuickActions.nonce,
      post_id: postId,
      field: field,
      value: value
    })
      .done(function(response) {
        if (!response || !response.success || !response.data) {
          alert(dalmoaAdminQuickActions.messages.error);
          return;
        }

        const data = response.data;

        if (field === 'is_featured' || field === 'is_active') {
          const selector = '.dalmoa-quick-toggle[data-post-id="' + postId + '"][data-field="' + field + '"]';
          const $target = $(selector).first();

          if ($target.length) {
            $target.text(data.display);
            $target.data('value', data.nextValue);
            $target.removeClass('button-primary button-secondary');
            $target.addClass(data.buttonClass);
          }
        }

        if (field === 'moderation_status') {
          const selector = '.dalmoa-quick-status[data-post-id="' + postId + '"]';
          $(selector).each(function() {
            const $statusButton = $(this);
            const buttonValue = $statusButton.data('value');

            $statusButton.removeClass('button-primary');
            if (buttonValue === data.currentValue) {
              $statusButton.addClass('button-primary');
            }
          });
        }
      })
      .fail(function() {
        alert(dalmoaAdminQuickActions.messages.error);
      })
      .always(function() {
        $button.data('loading', false);
        $button.css('opacity', '1');
      });
  });
});
JS);
        });

        add_action('wp_ajax_dalmoa_admin_quick_action', function (): void {
            if (!current_user_can('edit_posts')) {
                wp_send_json_error(['message' => 'Forbidden'], 403);
            }

            check_ajax_referer('dalmoa_admin_quick_action', 'nonce');

            $postId = isset($_POST['post_id']) ? (int) $_POST['post_id'] : 0;
            $field = isset($_POST['field']) ? sanitize_key((string) $_POST['field']) : '';
            $value = isset($_POST['value']) ? sanitize_text_field((string) $_POST['value']) : '';

            if ($postId <= 0 || $field === '') {
                wp_send_json_error(['message' => 'Invalid request'], 400);
            }

            $allowedFields = ['is_featured', 'is_active', 'moderation_status'];

            if (!in_array($field, $allowedFields, true)) {
                wp_send_json_error(['message' => 'Invalid field'], 400);
            }

            if ($field === 'moderation_status') {
                $allowedStatus = ['approved', 'pending', 'rejected'];

                if (!in_array($value, $allowedStatus, true)) {
                    wp_send_json_error(['message' => 'Invalid status'], 400);
                }

                update_post_meta($postId, 'moderation_status', $value);

                wp_send_json_success([
                    'field' => $field,
                    'currentValue' => $value,
                ]);
            }

            $boolValue = in_array($value, ['1', 'true', 'yes', 'on'], true) ? '1' : '0';
            update_post_meta($postId, $field, $boolValue);

            $current = $boolValue === '1';
            $nextValue = $current ? '0' : '1';

            wp_send_json_success([
                'field' => $field,
                'currentValue' => $current ? '1' : '0',
                'nextValue' => $nextValue,
                'display' => $current ? 'ON' : 'OFF',
                'buttonClass' => $current ? 'button-primary' : 'button-secondary',
            ]);
        });
    }

    public static function renderBooleanToggle(int $postId, string $field, bool $current): string
    {
        $label = $current ? 'ON' : 'OFF';
        $buttonClass = $current ? 'button-primary' : 'button-secondary';
        $nextValue = $current ? '0' : '1';

        return sprintf(
            '<button type="button" class="button %1$s dalmoa-quick-toggle" data-post-id="%2$d" data-field="%3$s" data-value="%4$s">%5$s</button>',
            esc_attr($buttonClass),
            $postId,
            esc_attr($field),
            esc_attr($nextValue),
            esc_html($label)
        );
    }

    public static function renderStatusButtons(int $postId, string $currentStatus): string
    {
        $statuses = ['approved', 'pending', 'rejected'];
        $buttons = [];

        foreach ($statuses as $value) {
            $class = $value === $currentStatus ? 'button button-primary' : 'button';
            $buttons[] = sprintf(
                '<button type="button" class="%1$s dalmoa-quick-status" data-post-id="%2$d" data-field="moderation_status" data-value="%3$s">%4$s</button>',
                esc_attr($class),
                $postId,
                esc_attr($value),
                esc_html(DisplayLabel::moderationStatus($value))
            );
        }

        return '<div style="display:flex;gap:6px;flex-wrap:wrap;">' . implode('', $buttons) . '</div>';
    }
}