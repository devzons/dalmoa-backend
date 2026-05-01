<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Support;

final class FieldDefinition
{
    public function __construct(
        public readonly string $key,
        public readonly string $type,
        public readonly string $label,
        public readonly string $description = '',
        public readonly bool $required = false,
        public readonly mixed $default = null,
        public readonly bool $showInRest = true,
        public readonly bool $translatable = false,
        public readonly bool $publicApi = true,
        public readonly string $adminSection = 'general',
        public readonly array $choices = [],
        public readonly ?string $placeholder = null,
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly string|int|float|null $step = null,
        public readonly int $rows = 5,
        public readonly ?string $helpText = null,
    ) {}
}