<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Admin\Renderers;

use DalmoaCore\Fields\Support\FieldDefinition;

final class TextareaRenderer
{
    public function render(FieldDefinition $field, mixed $value): void
    {
        echo '<div style="margin-bottom:14px;">';
        echo '<label for="' . esc_attr($field->key) . '" style="display:block;font-weight:600;margin-bottom:6px;">' . esc_html($field->label) . '</label>';
        echo '<textarea class="widefat" rows="5" id="' . esc_attr($field->key) . '" name="dalmoa_fields[' . esc_attr($field->key) . ']">' . esc_textarea((string) $value) . '</textarea>';
        if ($field->description !== '') {
            echo '<p class="description">' . esc_html($field->description) . '</p>';
        }
        echo '</div>';
    }
}