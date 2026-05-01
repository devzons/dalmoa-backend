<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Api\Transformers\LoanTransformer;
use DalmoaCore\Localization\LocaleResolver;
use DalmoaCore\Support\Response;
use DalmoaCore\Support\Services\LoanService;

final class LoanController
{
    public function __construct(
        private readonly LoanService $service = new LoanService(),
        private readonly LoanTransformer $transformer = new LoanTransformer(),
        private readonly LocaleResolver $localeResolver = new LocaleResolver(),
    ) {}

    public function index(\WP_REST_Request $request)
    {
        $locale = $this->localeResolver->resolve(
            $request->get_param('lang') ?? $request->get_param('locale')
        );

        $filters = [
            'q' => $this->stringOrNull($request->get_param('q')),
            'featured' => $this->toBool($request->get_param('featured')),
            'region' => $this->stringOrNull($request->get_param('region')),
            'category' => $this->stringOrNull($request->get_param('category')),
            'price_min' => $this->toIntOrNull($request->get_param('price_min')),
            'price_max' => $this->toIntOrNull($request->get_param('price_max')),
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

    public function show(\WP_REST_Request $request)
    {
        $slug = (string) $request->get_param('slug');
        $locale = $this->localeResolver->resolve($request->get_param('locale'));

        $post = $this->service->findBySlug($slug);

        if (!$post instanceof \WP_Post) {
            return Response::notFound('Loan item not found');
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

    private function toIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = preg_replace('/[^\d]/', '', $value);
        }

        if ($value === '' || $value === null) {
            return null;
        }

        return max(0, (int) $value);
    }

    private function toPage(mixed $value): int
    {
        $page = (int) $value;
        return $page > 0 ? $page : 1;
    }
}