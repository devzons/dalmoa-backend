<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Api\Transformers\AdTransformer;
use DalmoaCore\Ads\AbTesting\Repositories\AdVariantRepository;
use DalmoaCore\Localization\LocaleResolver;
use DalmoaCore\Support\Response;
use DalmoaCore\Support\Services\AdService;

final class AdController
{
    public function __construct(
        private readonly AdService $service = new AdService(),
        private readonly AdTransformer $transformer = new AdTransformer(),
        private readonly LocaleResolver $localeResolver = new LocaleResolver(),
        private readonly AdVariantRepository $variants = new AdVariantRepository(),
    ) {}

    public function index(\WP_REST_Request $request): \WP_REST_Response
    {
        $locale = $this->localeResolver->resolve($request->get_param('locale') ?: $request->get_param('lang'));

        $filters = [
            'category' => $this->stringOrNull($request->get_param('category')),
            'q' => $this->stringOrNull($request->get_param('q')),
            'placement' => $this->stringOrNull($request->get_param('placement')),
        ];

        $grouped = $this->service->list($filters);

        return Response::json([
            'featured' => array_map(
                fn(\WP_Post $post): array => $this->transformer->transform($post, $locale),
                $grouped['featured']
            ),
            'standard' => array_map(
                fn(\WP_Post $post): array => $this->transformer->transform($post, $locale),
                $grouped['standard']
            ),
        ]);
    }

    public function sidebar(\WP_REST_Request $request): \WP_REST_Response
    {
        $locale = $this->localeResolver->resolve($request->get_param('locale') ?: $request->get_param('lang'));

        // 🔥 여러 개 반환 (최대 3개)
        $posts = $this->service->sidebarMultiple([
            'category' => $this->stringOrNull($request->get_param('category')),
            'q' => $this->stringOrNull($request->get_param('q')),
        ], 3);

        return Response::json([
            'items' => array_map(
                fn(\WP_Post $post): array => $this->transformer->transform($post, $locale),
                $posts
            ),
        ]);
    }

    public function featured(\WP_REST_Request $request): \WP_REST_Response
    {
        $locale = $this->localeResolver->resolve($request->get_param('locale') ?: $request->get_param('lang'));

        $posts = $this->service->featured([
            'q' => $this->stringOrNull($request->get_param('q')),
            'placement' => $this->stringOrNull($request->get_param('placement')),
        ]);

        return Response::json(array_map(
            fn(\WP_Post $post): array => $this->transformer->transform($post, $locale),
            $posts
        ));
    }

    public function show(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $slug = (string) $request->get_param('slug');
        $locale = $this->localeResolver->resolve($request->get_param('locale') ?: $request->get_param('lang'));

        $post = $this->service->findBySlug($slug);

        if (!$post instanceof \WP_Post) {
            return Response::notFound('Ad not found');
        }

        return Response::json($this->transformer->transform($post, $locale));
    }

    public function search(\WP_REST_Request $request): \WP_REST_Response
    {
        $locale = $this->localeResolver->resolve($request->get_param('locale') ?: $request->get_param('lang'));
        $q = $this->stringOrNull($request->get_param('q')) ?? '';

        $posts = $this->service->search($q);

        return Response::json(array_map(
            fn(\WP_Post $post): array => $this->transformer->transform($post, $locale),
            $posts
        ));
    }

    public function click(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->trackEvent($request, 'click');
    }

    public function impression(\WP_REST_Request $request): \WP_REST_Response
    {
        return $this->trackEvent($request, 'impression');
    }

    public function track(\WP_REST_Request $request): \WP_REST_Response
    {
        $type = $this->stringOrNull($request->get_param('type'));

        if (!in_array($type, ['click', 'impression'], true)) {
            return Response::error('Invalid tracking type', 400);
        }

        return $this->trackEvent($request, $type);
    }

    private function trackEvent(\WP_REST_Request $request, string $type): \WP_REST_Response
    {
        $postId = (int) ($request->get_param('id') ?: $request->get_param('adId'));
        $placement = $this->stringOrNull($request->get_param('placement')) ?? 'unknown';
        $variantId = sanitize_key((string) $request->get_param('variantId'));

        if ($postId <= 0 || get_post_type($postId) !== 'ad_listing') {
            return Response::error('Invalid ad', 400);
        }

        $totalMetaKey = $type === 'click' ? 'click_count' : 'impression_count';
        $placementMetaKey = sprintf('%s_%s', $totalMetaKey, sanitize_key($placement));

        $total = (int) get_post_meta($postId, $totalMetaKey, true);
        $placementTotal = (int) get_post_meta($postId, $placementMetaKey, true);

        update_post_meta($postId, $totalMetaKey, $total + 1);
        update_post_meta($postId, $placementMetaKey, $placementTotal + 1);

        if ($variantId !== '') {
            if ($type === 'click') {
                $this->variants->incrementClick($postId, $variantId);
            } else {
                $this->variants->incrementImpression($postId, $variantId);
            }
        }

        return Response::json([
            'ok' => true,
            'type' => $type,
            'placement' => $placement,
            'variantId' => $variantId !== '' ? $variantId : null,
            $type === 'click' ? 'clickCount' : 'impressionCount' => $total + 1,
            'placementCount' => $placementTotal + 1,
        ]);
    }

    private function stringOrNull(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';
        return $value !== '' ? $value : null;
    }
}