<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\AbTesting\DTO;

final class AdVariant
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $imageUrl,
        public readonly ?string $targetUrl,
        public readonly int $weight,
        public readonly int $impressions,
        public readonly int $clicks,
        public readonly bool $enabled = true,
    ) {}

    public function ctr(): float
    {
        if ($this->impressions <= 0) {
            return 0.0;
        }

        return $this->clicks / $this->impressions;
    }

    public function isReady(int $minImpressions = 100): bool
    {
        return $this->impressions >= $minImpressions;
    }

    public function withIncrementedImpression(): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            description: $this->description,
            imageUrl: $this->imageUrl,
            targetUrl: $this->targetUrl,
            weight: $this->weight,
            impressions: $this->impressions + 1,
            clicks: $this->clicks,
            enabled: $this->enabled,
        );
    }

    public function withIncrementedClick(): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            description: $this->description,
            imageUrl: $this->imageUrl,
            targetUrl: $this->targetUrl,
            weight: $this->weight,
            impressions: $this->impressions,
            clicks: $this->clicks + 1,
            enabled: $this->enabled,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'imageUrl' => $this->imageUrl,
            'targetUrl' => $this->targetUrl,
            'weight' => $this->weight,
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'ctr' => $this->ctr(),
            'enabled' => $this->enabled,
        ];
    }
}