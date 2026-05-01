<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Admin;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;

final class MetaBoxRegistrar
{
    public function __construct(
        private readonly MetaBoxRenderer $renderer,
    ) {}

    /**
     * @param array<class-string> $schemaClasses
     */
    public function register(array $schemaClasses): void
    {
        foreach ($schemaClasses as $schemaClass) {
            if (!is_subclass_of($schemaClass, FieldSchemaInterface::class)) {
                continue;
            }

            $postType = $schemaClass::postType();

            add_meta_box(
                'dalmoa_meta_fields',
                'Dalmoa Fields',
                function (\WP_Post $post) use ($schemaClass): void {
                    $this->renderer->render($post, $schemaClass);
                },
                $postType,
                'normal',
                'high'
            );
        }
    }
}