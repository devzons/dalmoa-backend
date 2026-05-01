<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\Support;

final class AdMetaKeys
{
    // Core flags
    public const IS_ACTIVE = 'is_active';
    public const IS_PAID = 'is_paid';
    public const IS_FEATURED = 'is_featured';

    // Plan & priority
    public const AD_PLAN = 'ad_plan';
    public const PRIORITY_SCORE = 'priority_score';

    // Scheduling
    public const AD_STARTS_AT = 'ad_starts_at';
    public const AD_ENDS_AT = 'ad_ends_at';
    public const EXPIRES_AT = 'expires_at';

    // Moderation
    public const MODERATION_STATUS = 'moderation_status';

    // Tracking
    public const IMPRESSION_COUNT = 'impression_count';
    public const CLICK_COUNT = 'click_count';

    // Bidding
    public const BID_AMOUNT = 'bid_amount';
    public const AUTO_BID_ENABLED = 'auto_bid_enabled';
    public const AUTO_BID_MAX = 'auto_bid_max';

    // Frequency cap
    public const FREQUENCY_CAP = 'frequency_cap';

    // CTR cache (optional)
    public const CTR = 'ctr';

    // Placement
    public const PLACEMENT = 'placement';
}