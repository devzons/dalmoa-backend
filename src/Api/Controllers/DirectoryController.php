<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Api\Transformers\DirectoryTransformer;
use DalmoaCore\Localization\LocaleResolver;
use DalmoaCore\Support\Response;
use DalmoaCore\Support\Services\DirectoryService;

final class DirectoryController
{
    public function __construct(
        private readonly DirectoryService $service = new DirectoryService(),
        private readonly DirectoryTransformer $transformer = new DirectoryTransformer(),
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
            'featured' => $this->toBool($request->get_param('featured')),
            'sort' => $this->stringOrNull($request->get_param('sort')),
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
            return Response::notFound('Directory not found');
        }

        $this->service->incrementViews($post->ID);

        return Response::json($this->transformer->transform($post, $locale));
    }

    private function stringOrNull(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value !== '' ? $value : null;
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = is_string($value) ? trim($value) : (is_scalar($value) ? (string) $value : '');

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function toPage(mixed $value): int
    {
        $page = is_numeric($value) ? (int) $value : 1;

        return max(1, $page);
    }
}