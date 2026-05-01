<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Validation;

use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class Validator
{
    public function validate(FieldDefinition $field, mixed $value): array
    {
        $errors = [];

        if ($field->required && ($value === null || $value === '')) {
            $errors[] = sprintf('%s 필드는 필수입니다.', $field->label);
            return $errors;
        }

        if ($value === null || $value === '') {
            return $errors;
        }

        switch ($field->type) {
            case FieldType::EMAIL:
                if (!is_email((string) $value)) {
                    $errors[] = sprintf('%s 형식이 올바르지 않습니다.', $field->label);
                }
                break;

            case FieldType::URL:
                if (filter_var((string) $value, FILTER_VALIDATE_URL) === false) {
                    $errors[] = sprintf('%s URL 형식이 올바르지 않습니다.', $field->label);
                }
                break;

            case FieldType::SELECT:
                if ($field->choices !== [] && !array_key_exists((string) $value, $field->choices)) {
                    $errors[] = sprintf('%s 값이 허용되지 않습니다.', $field->label);
                }
                break;

            case FieldType::NUMBER:
                if (!is_numeric($value)) {
                    $errors[] = sprintf('%s 값은 숫자여야 합니다.', $field->label);
                }
                break;
        }

        return $errors;
    }
}