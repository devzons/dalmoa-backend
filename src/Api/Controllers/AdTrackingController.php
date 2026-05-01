<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Support\Response;
use DalmoaCore\Ads\AbTesting\Repositories\AdVariantRepository;

final class AdTrackingController
{
    public function __construct(
        private readonly AdVariantRepository $variants = new AdVariantRepository(),
    ) {}

    public function trackClick(\WP_REST_Request $request): \WP_REST_Response
    {
        $adId = (int) $request->get_param('adId');
        $variantId = sanitize_key((string) $request->get_param('variantId'));

        if ($adId <= 0) {
            return Response::error('Invalid adId', 400);
        }

        // 기존 ad click tracking
        $clicks = (int) get_post_meta($adId, 'click_count', true);
        update_post_meta($adId, 'click_count', $clicks + 1);

        // A/B variant tracking
        if ($variantId !== '') {
            $this->variants->incrementClick($adId, $variantId);
        }

        return Response::success([
            'tracked' => true,
        ]);
    }

    public function trackImpression(\WP_REST_Request $request): \WP_REST_Response
    {
        $adId = (int) $request->get_param('adId');
        $variantId = sanitize_key((string) $request->get_param('variantId'));

        if ($adId <= 0) {
            return Response::error('Invalid adId', 400);
        }

        // 기존 impression tracking
        $impressions = (int) get_post_meta($adId, 'impression_count', true);
        update_post_meta($adId, 'impression_count', $impressions + 1);

        // A/B variant tracking
        if ($variantId !== '') {
            $this->variants->incrementImpression($adId, $variantId);
        }

        return Response::success([
            'tracked' => true,
        ]);
    }
}