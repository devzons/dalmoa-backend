<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Validation;

use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class Sanitizer
{
    public function sanitize(FieldDefinition $field, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($field->type) {
            FieldType::TEXT,
            FieldType::PHONE => sanitize_text_field((string) $value),

            FieldType::TEXTAREA => sanitize_textarea_field((string) $value),

            FieldType::EMAIL => sanitize_email((string) $value),

            FieldType::URL => esc_url_raw((string) $value),

            FieldType::NUMBER => is_numeric($value) ? (float) $value : null,

            FieldType::BOOLEAN => (bool) $value,

            FieldType::SELECT => sanitize_text_field((string) $value),

            FieldType::IMAGE => absint($value),

            default => sanitize_text_field((string) $value),
        };
    }
}