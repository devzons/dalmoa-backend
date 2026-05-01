<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Transformers;

use DalmoaCore\Localization\LocaleResolver;

final class TownBoardTransformer
{
    public function __construct(
        private readonly LocaleResolver $localeResolver = new LocaleResolver(),
    ) {}

    public function transform(\WP_Post $post, string $locale = 'ko'): array
    {
        $locale = $this->localeResolver->resolve($locale);
        $thumbnailId = (int) get_post_meta($post->ID, 'thumbnail_id', true);

        return [
            'id' => $post->ID,
            'slug' => $post->post_name,
            'title' => $this->localized($post->ID, 'title', $locale, $post->post_title),
            'excerpt' => $this->nullable($this->localized($post->ID, 'excerpt', $locale, $post->post_excerpt)),
            'content' => $this->nullable($this->localized($post->ID, 'content', $locale, $post->post_content)),
            'thumbnailUrl' => $thumbnailId > 0
                ? $this->absoluteUrl(wp_get_attachment_image_url($thumbnailId, 'large'))
                : null,
            'boardCategory' => $this->nullable($this->localized($post->ID, 'board_category', $locale)),
            'authorName' => $this->meta($post->ID, 'author_name'),
            'contactEmail' => $this->meta($post->ID, 'contact_email'),
            'contactPhone' => $this->meta($post->ID, 'contact_phone'),
            'publishedAt' => get_the_date('c', $post),
            'isFeatured' => (bool) get_post_meta($post->ID, 'is_featured', true),
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