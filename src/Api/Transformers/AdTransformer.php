<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Transformers;

use DalmoaCore\Localization\LocaleResolver;
use DalmoaCore\Support\Services\AdPriorityCalculator;

final class AdTransformer
{
    public function __construct(
        private readonly LocaleResolver $localeResolver = new LocaleResolver(),
        private readonly AdPriorityCalculator $calculator = new AdPriorityCalculator(),
    ) {}

    public function transform(\WP_Post $post, string $locale = 'ko'): array
    {
        $locale = $this->localeResolver->resolve($locale);
        $thumbnailId = (int) get_post_meta($post->ID, 'thumbnail_id', true);

        $adPlan = $this->calculator->plan($post->ID);
        $adPriority = $this->calculator->priority($post->ID);
        $isAdActive = $this->calculator->isActive($post->ID);
        $isFeatured = $this->calculator->isFeatured($post->ID);
        $isPaid = $this->calculator->isPaid($post->ID);

        return [
            'id' => $post->ID,
            'slug' => $post->post_name,
            'title' => $post->post_title ?: $this->localized($post->ID, 'title', $locale, ''),
            'excerpt' => $this->nullable($post->post_excerpt ?: $this->localized($post->ID, 'excerpt', $locale, '')),
            'thumbnailUrl' => $this->absoluteUrl($post->imageUrl ?? null) ?? (
                $thumbnailId > 0
                    ? $this->absoluteUrl(wp_get_attachment_image_url($thumbnailId, 'large'))
                    : null
            ),
            'ctaLabel' => $this->localized($post->ID, 'cta_label', $locale, '자세히 보기'),
            'ctaUrl' => $this->absoluteUrl($post->targetUrl ?? null) ?? $this->meta($post->ID, 'cta_url') ?? '/ads',
            'isExternal' => get_post_meta($post->ID, 'target_external', true) === '1',
            'region' => $this->meta($post->ID, 'region_key'),

            'isPaid' => $isPaid,
            'isFeatured' => $isFeatured,
            'featured' => $isFeatured,
            'adPlan' => $adPlan,
            'adPriority' => $adPriority,
            'priority' => $adPlan,
            'isAdActive' => $isAdActive,

            'is_active' => $isAdActive,
            'is_paid' => $isPaid,
            'is_featured' => $isFeatured,
            'priority_score' => $adPriority,

            'payment_status' => $this->meta($post->ID, 'payment_status'),
            'billing_type' => $this->meta($post->ID, 'billing_type'),

            'subscription_status' => $this->meta($post->ID, 'subscription_status'),
            'subscription_cancel_at_period_end' => $this->meta($post->ID, 'subscription_cancel_at_period_end') ?? '0',
            'subscription_current_period_start' => $this->meta($post->ID, 'subscription_current_period_start'),
            'subscription_current_period_end' => $this->meta($post->ID, 'subscription_current_period_end'),

            'adStartsAt' => $this->meta($post->ID, 'ad_starts_at'),
            'adEndsAt' => $this->meta($post->ID, 'ad_ends_at'),
            'expiresAt' => $this->meta($post->ID, 'expires_at'),

            'ad_starts_at' => $this->meta($post->ID, 'ad_starts_at'),
            'ad_ends_at' => $this->meta($post->ID, 'ad_ends_at'),
            'expires_at' => $this->meta($post->ID, 'expires_at'),

            'clickCount' => (int) get_post_meta($post->ID, 'click_count', true),
            'impressionCount' => (int) get_post_meta($post->ID, 'impression_count', true),

            'abTest' => $post->abTest ?? null,
        ];
    }

    private function localized(int $postId, string $baseKey, string $locale, string $fallback = ''): string
    {
        $value = (string) get_post_meta($postId, "{$baseKey}_{$locale}", true);

        if ($value !== '') {
            return $value;
        }

        $alternate = (string) get_post_meta($postId, "{$baseKey}_{$this->localeResolver->alternate($locale)}", true);

        if ($alternate !== '') {
            return $alternate;
        }

        return $fallback;
    }

    private function absoluteUrl(string|false|null $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return home_url($url);
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
}