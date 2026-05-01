<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Transformers;

use DalmoaCore\Localization\LocaleResolver;
use DalmoaCore\Support\Labels\DisplayLabel;

final class RealEstateTransformer
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

        $adPlan = $this->meta($post->ID, 'ad_plan');
        $adPriority = (int) get_post_meta($post->ID, 'priority_score', true);
        $isPaid = $this->truthy(get_post_meta($post->ID, 'is_paid', true));
        $isFeatured = $this->truthy(get_post_meta($post->ID, 'is_featured', true));

        return [
            'id' => $post->ID,
            'slug' => $post->post_name,
            'title' => $this->localized($post->ID, 'title', $locale, $post->post_title),
            'excerpt' => $this->nullable($this->localized($post->ID, 'excerpt', $locale, $post->post_excerpt)),
            'content' => $this->nullable($this->localized($post->ID, 'content', $locale, $post->post_content)),
            'thumbnailUrl' => $thumbnailId > 0
                ? $this->absoluteUrl(wp_get_attachment_image_url($thumbnailId, 'large'))
                : null,

            'listingType' => $this->enumLabel('listing_type', $post->ID, $locale),
            'propertyType' => $this->enumLabel('property_type', $post->ID, $locale),
            'priceLabel' => $this->nullable($this->localized($post->ID, 'price_label', $locale)),
            'bedrooms' => $this->meta($post->ID, 'bedrooms'),
            'bathrooms' => $this->meta($post->ID, 'bathrooms'),
            'propertyLocation' => $this->nullable($this->localized($post->ID, 'property_location', $locale)),
            'contactEmail' => $this->meta($post->ID, 'contact_email'),
            'contactPhone' => $this->meta($post->ID, 'contact_phone'),
            'contactUrl' => $this->meta($post->ID, 'contact_url'),
            'publishedAt' => get_the_date('c', $post),

            'adPlan' => $adPlan,
            'adPriority' => $adPriority,
            'isPaid' => $isPaid,
            'isFeatured' => $isFeatured,
            'featured' => $isFeatured,
        ];
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

    private function enumLabel(string $key, int $postId, string $locale): ?string
    {
        $value = (string) get_post_meta($postId, $key, true);

        if ($value === '') {
            return null;
        }

        return match ($key) {
            'listing_type' => DisplayLabel::listingType($value, $locale),
            'property_type' => DisplayLabel::propertyType($value, $locale),
            default => $value,
        };
    }
}