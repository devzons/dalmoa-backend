<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Admin\Renderers;

use DalmoaCore\Fields\Support\FieldDefinition;

final class SelectRenderer
{
    public function render(FieldDefinition $field, mixed $value): void
    {
        echo '<div style="margin-bottom:14px;">';
        echo '<label for="' . esc_attr($field->key) . '" style="display:block;font-weight:600;margin-bottom:6px;">' . esc_html($field->label) . '</label>';
        echo '<select class="widefat" id="' . esc_attr($field->key) . '" name="dalmoa_fields[' . esc_attr($field->key) . ']">';

        foreach ($field->choices as $choiceValue => $choiceLabel) {
            echo '<option value="' . esc_attr((string) $choiceValue) . '" ' . selected((string) $value, (string) $choiceValue, false) . '>' . esc_html((string) $choiceLabel) . '</option>';
        }

        echo '</select>';
        if ($field->description !== '') {
            echo '<p class="description">' . esc_html($field->description) . '</p>';
        }
        echo '</div>';
    }
}