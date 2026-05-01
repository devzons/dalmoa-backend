<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\AbTesting\Services;

use DalmoaCore\Ads\AbTesting\DTO\AdVariant;
use DalmoaCore\Ads\AbTesting\Repositories\AdVariantRepository;
use DalmoaCore\Ads\AbTesting\Support\AdAbTestMetaKeys;

final class AdAbTestService
{
    public function __construct(
        private readonly AdVariantRepository $variants,
        private readonly AdVariantSelector $selector,
    ) {}

    public function applyVariant(array $ad): array
    {
        $adId = (int)($ad['id'] ?? 0);

        if ($adId <= 0 || !$this->variants->isEnabled($adId)) {
            return $ad;
        }

        $variants = $this->variants->getVariants($adId);

        if ($variants === []) {
            return $ad;
        }

        $selected = $this->selectVariant($adId, $variants);

        if (!$selected) {
            return $ad;
        }

        return array_merge($ad, [
            'title' => $selected->title !== '' ? $selected->title : ($ad['title'] ?? ''),
            'description' => $selected->description ?? ($ad['description'] ?? null),
            'imageUrl' => $selected->imageUrl ?? ($ad['imageUrl'] ?? null),
            'targetUrl' => $selected->targetUrl ?? ($ad['targetUrl'] ?? null),
            'abTest' => [
                'enabled' => true,
                'variantId' => $selected->id,
                'strategy' => $this->variants->getStrategy($adId),
            ],
        ]);
    }

    public function trackImpression(int $adId, ?string $variantId): void
    {
        if ($adId <= 0 || !$variantId) {
            return;
        }

        $this->variants->incrementImpression($adId, $variantId);
    }

    public function trackClick(int $adId, ?string $variantId): void
    {
        if ($adId <= 0 || !$variantId) {
            return;
        }

        $this->variants->incrementClick($adId, $variantId);
    }

    /**
     * @param AdVariant[] $variants
     */
    private function selectVariant(int $adId, array $variants): ?AdVariant
    {
        $winnerId = $this->variants->getWinnerVariantId($adId);

        if ($winnerId) {
            foreach ($variants as $variant) {
                if ($variant->id === $winnerId && $variant->enabled) {
                    return $variant;
                }
            }
        }

        $strategy = $this->variants->getStrategy($adId);

        if ($strategy === AdAbTestMetaKeys::STRATEGY_AUTO_CTR) {
            $winner = $this->selector->selectBestCtr($variants);

            if ($winner && $winner->impressions >= 100) {
                $this->variants->saveWinnerVariantId($adId, $winner->id);
            }

            return $winner;
        }

        return $this->selector->selectWeighted($variants);
    }
}