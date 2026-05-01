<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\AbTesting\Services;

use DalmoaCore\Ads\AbTesting\DTO\AdVariant;
use DalmoaCore\Ads\AbTesting\Repositories\AdVariantRepository;
use DalmoaCore\Ads\AbTesting\Support\AdAbTestMetaKeys;

final class AdAbTestService
{
    private const MIN_WINNER_IMPRESSIONS = 100;
    private const MIN_TOTAL_IMPRESSIONS = 200;
    private const MIN_CTR_LIFT = 0.10;

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
        $variantId = sanitize_key((string) $variantId);

        if ($adId <= 0 || $variantId === '') {
            return;
        }

        $this->variants->incrementImpression($adId, $variantId);
    }

    public function trackClick(int $adId, ?string $variantId): void
    {
        $variantId = sanitize_key((string) $variantId);

        if ($adId <= 0 || $variantId === '') {
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

        if ($strategy !== AdAbTestMetaKeys::STRATEGY_AUTO_CTR) {
            return $this->selector->selectWeighted($variants);
        }

        $winner = $this->selector->selectBestCtr($variants, self::MIN_WINNER_IMPRESSIONS);

        if (!$winner) {
            return $this->selector->selectWeighted($variants);
        }

        if ($this->canLockWinner($winner, $variants)) {
            $this->variants->saveWinnerVariantId($adId, $winner->id);

            return $winner;
        }

        return $this->selector->selectWeighted($variants);
    }

    /**
     * @param AdVariant[] $variants
     */
    private function canLockWinner(AdVariant $winner, array $variants): bool
    {
        $enabled = array_values(array_filter(
            $variants,
            static fn (AdVariant $variant): bool => $variant->enabled
        ));

        if (count($enabled) < 2) {
            return false;
        }

        $totalImpressions = array_sum(array_map(
            static fn (AdVariant $variant): int => $variant->impressions,
            $enabled
        ));

        if ($totalImpressions < self::MIN_TOTAL_IMPRESSIONS || !$winner->isReady(self::MIN_WINNER_IMPRESSIONS)) {
            return false;
        }

        foreach ($enabled as $variant) {
            if ($variant->id === $winner->id || !$variant->isReady(self::MIN_WINNER_IMPRESSIONS)) {
                continue;
            }

            if ($winner->ctr() < $variant->ctr() * (1 + self::MIN_CTR_LIFT)) {
                return false;
            }
        }

        return true;
    }
}