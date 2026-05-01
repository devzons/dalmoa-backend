<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Api\Transformers\TownBoardTransformer;
use DalmoaCore\Localization\LocaleResolver;
use DalmoaCore\Support\Response;
use DalmoaCore\Support\Services\TownBoardService;

final class TownBoardController
{
    public function __construct(
        private readonly TownBoardService $service = new TownBoardService(),
        private readonly TownBoardTransformer $transformer = new TownBoardTransformer(),
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
        $locale = $this->localeResolver->resolve($request->get_param('locale'));

        $post = $this->service->findBySlug($slug);

        if (!$post instanceof \WP_Post) {
            return Response::notFound('Town board item not found');
        }

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
        $page = (int) $value;
        return $page > 0 ? $page : 1;
    }    
}