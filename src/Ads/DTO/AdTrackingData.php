<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\DTO;

final class AdTrackingData
{
    public function __construct(
        public readonly int $adId,
        public readonly int $impressions,
        public readonly int $clicks,
        public readonly ?string $placement = null,
    ) {}

    public function ctr(): float
    {
        if ($this->impressions <= 0) {
            return 0.0;
        }

        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    public function toArray(): array
    {
        return [
            'adId' => $this->adId,
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'ctr' => $this->ctr(),
            'placement' => $this->placement,
        ];
    }
}