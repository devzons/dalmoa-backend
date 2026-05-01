<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Api\Transformers\AdTransformer;
use DalmoaCore\Api\Transformers\BusinessPageTransformer;
use DalmoaCore\Api\Transformers\BusinessSaleTransformer;
use DalmoaCore\Api\Transformers\CarTransformer;
use DalmoaCore\Api\Transformers\DirectoryTransformer;
use DalmoaCore\Api\Transformers\JobTransformer;
use DalmoaCore\Api\Transformers\LoanTransformer;
use DalmoaCore\Api\Transformers\MarketplaceTransformer;
use DalmoaCore\Api\Transformers\NewsTransformer;
use DalmoaCore\Api\Transformers\RealEstateTransformer;
use DalmoaCore\Api\Transformers\TownBoardTransformer;
use DalmoaCore\Localization\LocaleResolver;
use DalmoaCore\Support\Response;
use DalmoaCore\Support\Services\AdService;
use DalmoaCore\Support\Services\BusinessPageService;
use DalmoaCore\Support\Services\BusinessSaleService;
use DalmoaCore\Support\Services\CarService;
use DalmoaCore\Support\Services\DirectoryService;
use DalmoaCore\Support\Services\JobService;
use DalmoaCore\Support\Services\LoanService;
use DalmoaCore\Support\Services\MarketplaceService;
use DalmoaCore\Support\Services\NewsService;
use DalmoaCore\Support\Services\RealEstateService;
use DalmoaCore\Support\Services\TownBoardService;

final class SearchController
{
    private const PER_TYPE_LIMIT = 6;

    private const TYPE_WEIGHT = [
        'jobs' => 110,
        'business-sale' => 105,
        'loan' => 100,
        'marketplace' => 95,
        'real-estate' => 90,
        'cars' => 85,
        'directory' => 80,
        'news' => 70,
        'town-board' => 65,
        'business' => 60,
        'ad' => 50,
    ];

    public function __construct(
        private readonly DirectoryService $directoryService = new DirectoryService(),
        private readonly AdService $adService = new AdService(),
        private readonly BusinessPageService $businessPageService = new BusinessPageService(),
        private readonly NewsService $newsService = new NewsService(),
        private readonly JobService $jobService = new JobService(),
        private readonly LoanService $loanService = new LoanService(),
        private readonly MarketplaceService $marketplaceService = new MarketplaceService(),
        private readonly RealEstateService $realEstateService = new RealEstateService(),
        private readonly CarService $carService = new CarService(),
        private readonly TownBoardService $townBoardService = new TownBoardService(),
        private readonly BusinessSaleService $businessSaleService = new BusinessSaleService(),
        private readonly DirectoryTransformer $directoryTransformer = new DirectoryTransformer(),
        private readonly AdTransformer $adTransformer = new AdTransformer(),
        private readonly BusinessPageTransformer $businessPageTransformer = new BusinessPageTransformer(),
        private readonly NewsTransformer $newsTransformer = new NewsTransformer(),
        private readonly JobTransformer $jobTransformer = new JobTransformer(),
        private readonly LoanTransformer $loanTransformer = new LoanTransformer(),
        private readonly MarketplaceTransformer $marketplaceTransformer = new MarketplaceTransformer(),
        private readonly RealEstateTransformer $realEstateTransformer = new RealEstateTransformer(),
        private readonly CarTransformer $carTransformer = new CarTransformer(),
        private readonly TownBoardTransformer $townBoardTransformer = new TownBoardTransformer(),
        private readonly BusinessSaleTransformer $businessSaleTransformer = new BusinessSaleTransformer(),
        private readonly LocaleResolver $localeResolver = new LocaleResolver(),
    ) {}

    public function index(\WP_REST_Request $request): \WP_REST_Response
    {
        $locale = $this->localeResolver->resolve($request->get_param('locale'));
        $q = $this->stringOrNull($request->get_param('q')) ?? '';

        if ($q === '') {
            return Response::json([
                'q' => '',
                'total' => 0,
                'results' => [],
            ]);
        }

        $results = [];

        $this->appendResults($results, 'jobs', $this->unwrapItems($this->jobService->list(['q' => $q, 'page' => 1])), $this->jobTransformer, $locale, $q);
        $this->appendResults($results, 'business-sale', $this->unwrapItems($this->businessSaleService->list(['q' => $q, 'page' => 1])), $this->businessSaleTransformer, $locale, $q);
        $this->appendResults($results, 'loan', $this->unwrapItems($this->loanService->list(['q' => $q, 'page' => 1])), $this->loanTransformer, $locale, $q);
        $this->appendResults($results, 'marketplace', $this->unwrapItems($this->marketplaceService->list(['q' => $q, 'page' => 1])), $this->marketplaceTransformer, $locale, $q);
        $this->appendResults($results, 'real-estate', $this->unwrapItems($this->realEstateService->list(['q' => $q, 'page' => 1])), $this->realEstateTransformer, $locale, $q);
        $this->appendResults($results, 'cars', $this->unwrapItems($this->carService->list(['q' => $q, 'page' => 1])), $this->carTransformer, $locale, $q);
        $this->appendResults($results, 'directory', $this->unwrapItems($this->directoryService->list(['q' => $q])), $this->directoryTransformer, $locale, $q);
        $this->appendResults($results, 'news', $this->unwrapItems($this->newsService->list(['q' => $q])), $this->newsTransformer, $locale, $q);
        $this->appendResults($results, 'town-board', $this->unwrapItems($this->townBoardService->list(['q' => $q])), $this->townBoardTransformer, $locale, $q);
        $this->appendResults($results, 'business', $this->unwrapItems($this->businessPageService->search($q)), $this->businessPageTransformer, $locale, $q);
        $this->appendResults($results, 'ad', $this->unwrapItems($this->adService->search($q)), $this->adTransformer, $locale, $q);

        usort($results, function (array $a, array $b): int {
            return ($b['_score'] ?? 0) <=> ($a['_score'] ?? 0);
        });

        $results = array_map(function (array $result): array {
            unset($result['_score']);
            return $result;
        }, $results);

        return Response::json([
            'q' => $q,
            'total' => count($results),
            'results' => $results,
        ]);
    }

    private function appendResults(
        array &$results,
        string $type,
        array $posts,
        object $transformer,
        string $locale,
        string $q
    ): void {
        $rankedPosts = [];

        foreach ($posts as $post) {
            if (!$post instanceof \WP_Post) {
                continue;
            }

            $rankedPosts[] = [
                'post' => $post,
                'score' => $this->scorePost($post, $type, $q),
            ];
        }

        usort($rankedPosts, function (array $a, array $b): int {
            return $b['score'] <=> $a['score'];
        });

        $rankedPosts = array_slice($rankedPosts, 0, self::PER_TYPE_LIMIT);

        foreach ($rankedPosts as $rankedPost) {
            /** @var \WP_Post $post */
            $post = $rankedPost['post'];

            $results[] = [
                'type' => $type,
                'item' => $transformer->transform($post, $locale),
                '_score' => $rankedPost['score'],
            ];
        }
    }

    private function scorePost(\WP_Post $post, string $type, string $q): int
    {
        $query = mb_strtolower($q);
        $title = mb_strtolower((string) $post->post_title);
        $content = mb_strtolower(wp_strip_all_tags((string) $post->post_content));
        $excerpt = mb_strtolower(wp_strip_all_tags((string) $post->post_excerpt));

        $metaText = mb_strtolower($this->collectMetaText($post->ID));
        $taxonomyText = mb_strtolower($this->collectTaxonomyText($post->ID));
        $score = self::TYPE_WEIGHT[$type] ?? 0;

        if ($title === $query) {
            $score += 1000;
        } elseif (str_contains($title, $query)) {
            $score += 700;
        }

        if (str_contains($excerpt, $query)) {
            $score += 400;
        }

        if (str_contains($metaText, $query)) {
            $score += 300;
        }

        if (str_contains($taxonomyText, $query)) {
            $score += 500;
        }

        if (str_contains($content, $query)) {
            $score += 150;
        }

        if ((string) get_post_meta($post->ID, 'is_featured', true) === '1') {
            $score += 120;
        }

        $timestamp = strtotime($post->post_date);
        if ($timestamp !== false) {
            $daysOld = max(0, (int) floor((time() - $timestamp) / DAY_IN_SECONDS));
            $score += max(0, 90 - min(90, $daysOld));
        }

        return $score;
    }

    private function collectMetaText(int $postId): string
    {
        $meta = get_post_meta($postId);
        $values = [];

        foreach ($meta as $key => $items) {
            if (str_starts_with((string) $key, '_')) {
                continue;
            }

            foreach ((array) $items as $value) {
                if (is_scalar($value)) {
                    $values[] = (string) $value;
                }
            }
        }

        return implode(' ', $values);
    }

    private function collectTaxonomyText(int $postId): string
    {
        $taxonomies = get_post_taxonomies($postId);
        $values = [];

        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($postId, $taxonomy);

            if (!is_array($terms)) {
                continue;
            }

            foreach ($terms as $term) {
                $values[] = $term->name;
                $values[] = $term->slug;
                $values[] = $term->description;
            }
        }

        return implode(' ', array_filter($values));
    }

    private function unwrapItems(array $data): array
    {
        if (isset($data['items']) && is_array($data['items'])) {
            return $data['items'];
        }

        return $data;
    }

    private function stringOrNull(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';
        return $value !== '' ? $value : null;
    }
}