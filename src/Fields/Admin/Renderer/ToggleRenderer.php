<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Admin\Renderers;

use DalmoaCore\Fields\Support\FieldDefinition;

final class ToggleRenderer
{
    public function render(FieldDefinition $field, mixed $value): void
    {
        $checked = !empty($value);

        echo '<div style="margin-bottom:14px;">';
        echo '<label style="display:flex;align-items:center;gap:8px;font-weight:600;">';
        echo '<input type="hidden" name="dalmoa_fields[' . esc_attr($field->key) . ']" value="0" />';
        echo '<input type="checkbox" name="dalmoa_fields[' . esc_attr($field->key) . ']" value="1" ' . checked($checked, true, false) . ' />';
        echo esc_html($field->label);
        echo '</label>';
        if ($field->description !== '') {
            echo '<p class="description">' . esc_html($field->description) . '</p>';
        }
        echo '</div>';
    }
}