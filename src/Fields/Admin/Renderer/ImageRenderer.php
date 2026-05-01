<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Admin\Renderers;

use DalmoaCore\Fields\Support\FieldDefinition;

final class ImageRenderer
{
    public function render(FieldDefinition $field, mixed $value): void
    {
        echo '<div style="margin-bottom:14px;">';
        echo '<label for="' . esc_attr($field->key) . '" style="display:block;font-weight:600;margin-bottom:6px;">' . esc_html($field->label) . '</label>';
        echo '<input type="number" class="widefat" id="' . esc_attr($field->key) . '" name="dalmoa_fields[' . esc_attr($field->key) . ']" value="' . esc_attr((string) $value) . '" placeholder="Attachment ID"/>';
        echo '<p class="description">현재는 미디어 Attachment ID를 입력합니다.</p>';
        echo '</div>';
    }
}