<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Contracts;

interface FieldSchemaInterface
{
    public static function postType(): string;

    /**
     * @return array<int, \DalmoaCore\Fields\Support\FieldDefinition>
     */
    public static function fields(): array;
}