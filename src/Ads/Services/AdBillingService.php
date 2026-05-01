<?php
declare(strict_types=1);

namespace DalmoaCore\Ads\Services;

final class AdBillingService
{
    private const CPM_RATE = 5.0;   // $5 / 1000 impressions
    private const CPC_RATE = 0.5;   // $0.5 / click

    public function calculate(int $impressions, int $clicks): array
    {
        $cpmRevenue = ($impressions / 1000) * self::CPM_RATE;
        $cpcRevenue = $clicks * self::CPC_RATE;

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            'cpmRevenue' => round($cpmRevenue, 2),
            'cpcRevenue' => round($cpcRevenue, 2),
            'totalRevenue' => round($cpmRevenue + $cpcRevenue, 2),
        ];
    }
}