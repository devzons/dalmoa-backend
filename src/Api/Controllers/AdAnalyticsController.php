<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Support\Response;

final class AdAnalyticsController
{
    public function summary(\WP_REST_Request $request): \WP_REST_Response
    {
        $adId = (int) $request->get_param('adId');

        if ($adId <= 0 || get_post_type($adId) !== 'ad_listing') {
            return Response::error('Invalid ad', 400);
        }

        $impressions = (int) get_post_meta($adId, 'impression_count', true);
        $clicks = (int) get_post_meta($adId, 'click_count', true);

        return Response::json([
            'adId' => $adId,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'ctr' => $impressions > 0 ? round($clicks / $impressions, 4) : 0.0,
            'revenue' => 0,
            'cost' => 0,
            'remainingBudget' => 0,
        ]);
    }

    public function breakdown(\WP_REST_Request $request): \WP_REST_Response
    {
        $adId = (int) $request->get_param('adId');

        if ($adId <= 0 || get_post_type($adId) !== 'ad_listing') {
            return Response::error('Invalid ad', 400);
        }

        return Response::json([
            'adId' => $adId,
            'placements' => [],
        ]);
    }

    public function variants(\WP_REST_Request $request): \WP_REST_Response
    {
        $adId = (int) $request->get_param('adId');

        if ($adId <= 0 || get_post_type($adId) !== 'ad_listing') {
            return Response::error('Invalid ad', 400);
        }

        return Response::json([
            'adId' => $adId,
            'variants' => [],
        ]);
    }
}