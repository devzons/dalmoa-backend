<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Api\Transformers\NewsTransformer;
use DalmoaCore\Localization\LocaleResolver;
use DalmoaCore\Support\Response;
use DalmoaCore\Support\Services\NewsService;

final class NewsController
{
    public function __construct(
        private readonly NewsService $service = new NewsService(),
        private readonly NewsTransformer $transformer = new NewsTransformer(),
        private readonly LocaleResolver $localeResolver = new LocaleResolver(),
    ) {}

    public function index(\WP_REST_Request $request): \WP_REST_Response
    {
        $locale = $this->localeResolver->resolve(
            $request->get_param('lang') ?? $request->get_param('locale')
        );

        $filters = [
            'q' => $this->stringOrNull($request->get_param('q')),
            'category' => $this->stringOrNull($request->get_param('category')),
            'page' => $this->toPage($request->get_param('page')),
        ];

        $result = $this->service->list($filters);

        $items = array_map(
            fn(\WP_Post $post): array => $this->transformer->transform($post, $locale),
            $result['items']
        );

        return Response::json([
            'items' => $items,
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'totalPages' => $result['totalPages'],
        ]);
    }

    public function show(\WP_REST_Request $request): \WP_REST_Response|\WP_Error
    {
        $slug = (string) $request->get_param('slug');
        $locale = $this->localeResolver->resolve(
            $request->get_param('lang') ?? $request->get_param('locale')
        );

        $post = $this->service->findBySlug($slug);

        if (!$post instanceof \WP_Post) {
            return Response::notFound('News not found');
        }

        return Response::json($this->transformer->transform($post, $locale));
    }

    private function stringOrNull(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value !== '' ? $value : null;
    }

    private function toPage(mixed $value): int
    {
        $page = is_numeric($value) ? (int) $value : 1;

        return max(1, $page);
    }
}