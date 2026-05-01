<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\AbTesting\Repositories;

use DalmoaCore\Ads\AbTesting\DTO\AdVariant;
use DalmoaCore\Ads\AbTesting\Support\AdAbTestMetaKeys;

final class AdVariantRepository
{
    /**
     * @return AdVariant[]
     */
    public function getVariants(int $adId): array
    {
        $raw = get_post_meta($adId, AdAbTestMetaKeys::VARIANTS, true);

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($raw)) {
            return [];
        }

        $impressions = $this->getCounterMap($adId, AdAbTestMetaKeys::IMPRESSIONS);
        $clicks = $this->getCounterMap($adId, AdAbTestMetaKeys::CLICKS);

        $variants = [];

        foreach ($raw as $variant) {
            if (!is_array($variant)) {
                continue;
            }

            $id = sanitize_key((string) ($variant['id'] ?? ''));

            if ($id === '') {
                continue;
            }

            $variants[] = new AdVariant(
                id: $id,
                title: sanitize_text_field((string) ($variant['title'] ?? '')),
                description: isset($variant['description'])
                    ? sanitize_textarea_field((string) $variant['description'])
                    : null,
                imageUrl: isset($variant['imageUrl'])
                    ? esc_url_raw((string) $variant['imageUrl'])
                    : null,
                targetUrl: isset($variant['targetUrl'])
                    ? esc_url_raw((string) $variant['targetUrl'])
                    : null,
                weight: max(0, (int) ($variant['weight'] ?? 50)),
                impressions: max(0, (int) ($impressions[$id] ?? 0)),
                clicks: max(0, (int) ($clicks[$id] ?? 0)),
                enabled: isset($variant['enabled']) ? (bool) $variant['enabled'] : true,
            );
        }

        return $variants;
    }

    public function isEnabled(int $adId): bool
    {
        return get_post_meta($adId, AdAbTestMetaKeys::ENABLED, true) === '1';
    }

    public function getStrategy(int $adId): string
    {
        $strategy = sanitize_key((string) get_post_meta($adId, AdAbTestMetaKeys::STRATEGY, true));

        return in_array($strategy, [
            AdAbTestMetaKeys::STRATEGY_WEIGHTED,
            AdAbTestMetaKeys::STRATEGY_AUTO_CTR,
        ], true)
            ? $strategy
            : AdAbTestMetaKeys::STRATEGY_WEIGHTED;
    }

    public function getWinnerVariantId(int $adId): ?string
    {
        $winner = sanitize_key((string) get_post_meta($adId, AdAbTestMetaKeys::WINNER_VARIANT_ID, true));

        return $winner !== '' ? $winner : null;
    }

    public function saveWinnerVariantId(int $adId, string $variantId): void
    {
        $variantId = sanitize_key($variantId);

        if ($variantId === '') {
            return;
        }

        update_post_meta($adId, AdAbTestMetaKeys::WINNER_VARIANT_ID, $variantId);
    }

    public function incrementImpression(int $adId, string $variantId): void
    {
        $this->incrementCounter($adId, AdAbTestMetaKeys::IMPRESSIONS, $variantId);
    }

    public function incrementClick(int $adId, string $variantId): void
    {
        $this->incrementCounter($adId, AdAbTestMetaKeys::CLICKS, $variantId);
    }

    private function incrementCounter(int $adId, string $metaKey, string $variantId): void
    {
        $variantId = sanitize_key($variantId);

        if ($adId <= 0 || $variantId === '') {
            return;
        }

        $map = $this->getCounterMap($adId, $metaKey);
        $map[$variantId] = max(0, (int) ($map[$variantId] ?? 0)) + 1;

        update_post_meta($adId, $metaKey, $map);
    }

    private function getCounterMap(int $adId, string $metaKey): array
    {
        $map = get_post_meta($adId, $metaKey, true);

        return is_array($map) ? $map : [];
    }
}