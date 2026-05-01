<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\AbTesting\Support;

final class AdAbTestMetaKeys
{
    public const ENABLED = '_dalmoa_ab_enabled';
    public const STRATEGY = '_dalmoa_ab_strategy';
    public const VARIANTS = '_dalmoa_ab_variants';
    public const WINNER_VARIANT_ID = '_dalmoa_ab_winner_variant_id';

    public const IMPRESSIONS = '_dalmoa_ab_variant_impressions';
    public const CLICKS = '_dalmoa_ab_variant_clicks';

    public const STRATEGY_WEIGHTED = 'weighted';
    public const STRATEGY_AUTO_CTR = 'auto_ctr';

    public static function all(): array
    {
        return [
            self::ENABLED,
            self::STRATEGY,
            self::VARIANTS,
            self::WINNER_VARIANT_ID,
            self::IMPRESSIONS,
            self::CLICKS,
        ];
    }
}