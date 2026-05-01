<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Transformers;

use DalmoaCore\Localization\LocaleResolver;

final class DirectoryTransformer
{
    public function __construct(
        private readonly LocaleResolver $localeResolver = new LocaleResolver(),
    ) {}

    public function transform(\WP_Post $post, string $locale = 'ko'): array
    {
        $locale = $this->localeResolver->resolve($locale);

        $thumbnailId = (int) get_post_meta($post->ID, 'thumbnail_id', true);

        if ($thumbnailId <= 0) {
            $thumbnailId = (int) get_post_thumbnail_id($post->ID);
        }

        $adPlan = $this->meta($post->ID, 'ad_plan') ?? 'basic';
        $adPriority = (int) get_post_meta($post->ID, 'priority_score', true);
        $isPaid = $this->truthy(get_post_meta($post->ID, 'is_paid', true));
        $isFeatured = $this->truthy(get_post_meta($post->ID, 'is_featured', true));

        $adStartsAt = $this->meta($post->ID, 'ad_starts_at');
        $adEndsAt = $this->meta($post->ID, 'ad_ends_at');
        $isAdActive = $this->isActiveAd($adStartsAt, $adEndsAt);

        return [
            'id' => $post->ID,
            'slug' => $post->post_name,
            'title' => $this->localized($post->ID, 'title', $locale, $post->post_title),
            'excerpt' => $this->nullable($this->localized($post->ID, 'excerpt', $locale, $post->post_excerpt)),
            'content' => $this->nullable($this->localized($post->ID, 'content', $locale, $post->post_content)),
            'businessCategory' => $this->meta($post->ID, 'business_category'),
            'category' => $this->meta($post->ID, 'business_category'),
            'categoryLabel' => $this->categoryLabel($this->meta($post->ID, 'business_category')),
            'viewCount' => (int) get_post_meta($post->ID, 'view_count', true),
            'phone' => $this->meta($post->ID, 'phone'),
            'email' => $this->meta($post->ID, 'email'),
            'websiteUrl' => $this->meta($post->ID, 'website_url'),
            'address' => $this->metaLocalized($post->ID, 'address', $locale),
            'thumbnailUrl' => $thumbnailId > 0
                ? $this->absoluteUrl(wp_get_attachment_image_url($thumbnailId, 'large'))
                : null,
            'adPlan' => $adPlan,
            'adPriority' => $adPriority,
            'isPaid' => $isPaid,
            'isFeatured' => $isFeatured,
            'featured' => $isFeatured,
            'adStartsAt' => $adStartsAt,
            'adEndsAt' => $adEndsAt,
            'isAdActive' => $isAdActive,
        ];
    }

    private function categoryLabel(?string $category): ?string
    {
        if (!$category) {
            return null;
        }

        $labels = [
            'korean_restaurant' => '한식',
            'chinese_restaurant' => '중식',
            'japanese_restaurant' => '일식',
            'asian_cuisine' => '아시안',
            'fast_food' => '패스트푸드',
            'cafe_dessert' => '카페/디저트',
            'bakery' => '베이커리',
            'bar_pub' => '바/주점',
            'hair_salon' => '미용실',
            'medical_clinic' => '병원',
            'dental_clinic' => '치과',
            'attorney' => '변호사',
            'cpa_tax' => '회계/세무',
            'real_estate' => '부동산',
            'loan_mortgage' => '융자/모기지',
            'insurance' => '보험',
            'auto_repair' => '정비소',
            'moving' => '이사',
            'construction' => '건축/리모델링',
            'academy' => '학원',
            'church' => '교회',
            'grocery_store' => '마트',
            'local_services' => '생활서비스',
        ];

        return $labels[$category] ?? $category;
    }

    private function localized(int $postId, string $baseKey, string $locale, string $fallback = ''): string
    {
        $value = (string) get_post_meta($postId, "{$baseKey}_{$locale}", true);

        if ($value !== '') {
            return $value;
        }

        $alternate = (string) get_post_meta(
            $postId,
            "{$baseKey}_{$this->localeResolver->alternate($locale)}",
            true
        );

        if ($alternate !== '') {
            return $alternate;
        }

        return $fallback;
    }

    private function absoluteUrl(mixed $url): ?string
    {
        if (!is_string($url) || $url === '') {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return home_url($url);
    }

    private function metaLocalized(int $postId, string $baseKey, string $locale): ?string
    {
        return $this->nullable($this->localized($postId, $baseKey, $locale));
    }

    private function meta(int $postId, string $key): ?string
    {
        return $this->nullable((string) get_post_meta($postId, $key, true));
    }

    private function nullable(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';

        return $value !== '' ? $value : null;
    }

    private function truthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['1', 'true', 'yes', 'on', 'paid'], true);
    }

    private function isActiveAd(?string $startsAt, ?string $endsAt): bool
    {
        $now = current_time('timestamp');

        if ($startsAt) {
            $startsTimestamp = strtotime($startsAt);

            if ($startsTimestamp !== false && $startsTimestamp > $now) {
                return false;
            }
        }

        if ($endsAt) {
            $endsTimestamp = strtotime($endsAt);

            if ($endsTimestamp !== false && $endsTimestamp < $now) {
                return false;
            }
        }

        return true;
    }
}