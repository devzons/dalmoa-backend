<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\AbTesting\Services;

use DalmoaCore\Ads\AbTesting\DTO\AdVariant;

final class AdVariantSelector
{
    /**
     * @param AdVariant[] $variants
     */
    public function selectWeighted(array $variants): ?AdVariant
    {
        $enabled = array_values(array_filter(
            $variants,
            static fn (AdVariant $variant): bool => $variant->enabled && $variant->weight > 0
        ));

        if ($enabled === []) {
            return null;
        }

        $totalWeight = array_sum(array_map(
            static fn (AdVariant $variant): int => $variant->weight,
            $enabled
        ));

        if ($totalWeight <= 0) {
            return $enabled[0];
        }

        $random = random_int(1, $totalWeight);
        $cursor = 0;

        foreach ($enabled as $variant) {
            $cursor += $variant->weight;

            if ($random <= $cursor) {
                return $variant;
            }
        }

        return $enabled[0];
    }

    /**
     * @param AdVariant[] $variants
     */
    public function selectBestCtr(array $variants, int $minimumImpressions = 100): ?AdVariant
    {
        $enabled = array_values(array_filter(
            $variants,
            static fn (AdVariant $variant): bool => $variant->enabled
        ));

        if ($enabled === []) {
            return null;
        }

        usort(
            $enabled,
            static function (AdVariant $a, AdVariant $b) use ($minimumImpressions): int {
                $aReady = $a->isReady($minimumImpressions);
                $bReady = $b->isReady($minimumImpressions);

                if ($aReady !== $bReady) {
                    return $aReady ? -1 : 1;
                }

                if ($a->ctr() === $b->ctr()) {
                    return $b->impressions <=> $a->impressions;
                }

                return $b->ctr() <=> $a->ctr();
            }
        );

        return $enabled[0];
    }
}