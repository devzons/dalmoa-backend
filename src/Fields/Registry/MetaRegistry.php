<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Registry;

use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class MetaRegistry
{
    /**
     * @param array<int, class-string> $schemas
     */
    public function register(array $schemas): void
    {
        foreach ($schemas as $schemaClass) {
            $postType = $schemaClass::postType();

            foreach ($schemaClass::fields() as $field) {
                register_post_meta($postType, $field->key, [
                    'single' => true,
                    'type' => $this->resolveType($field),
                    'default' => $field->default,
                    'show_in_rest' => $field->showInRest,
                    'auth_callback' => static fn() => current_user_can('edit_posts'),
                ]);
            }
        }
    }

    private function resolveType(FieldDefinition $field): string
    {
        return match ($field->type) {
            FieldType::NUMBER => 'number',
            FieldType::BOOLEAN => 'boolean',
            default => 'string',
        };
    }
}