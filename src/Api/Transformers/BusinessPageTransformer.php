<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Transformers;

use DalmoaCore\Localization\LocaleResolver;

final class BusinessPageTransformer
{
    public function __construct(
        private readonly LocaleResolver $localeResolver = new LocaleResolver(),
    ) {}

    public function transform(\WP_Post $post, string $locale = 'ko'): array
    {
        $locale = $this->localeResolver->resolve($locale);
        $heroImageId = (int) get_post_meta($post->ID, 'hero_image_id', true);
        $aboutImageId = (int) get_post_meta($post->ID, 'about_image_id', true);

        $services = [];

        for ($i = 1; $i <= 3; $i++) {
            $title = $this->localized($post->ID, "service_{$i}_title", $locale);
            $body = $this->localized($post->ID, "service_{$i}_body", $locale);

            if ($title === '' && $body === '') {
                continue;
            }

            $services[] = [
                'title' => $title,
                'body' => $this->nullable($body),
            ];
        }

        return [
            'slug' => $post->post_name,
            'hero' => [
                'title' => $this->localized($post->ID, 'hero_title', $locale, $post->post_title),
                'subtitle' => $this->nullable($this->localized($post->ID, 'hero_subtitle', $locale)),
                'imageUrl' => $heroImageId > 0
                    ? $this->absoluteUrl(wp_get_attachment_image_url($heroImageId, 'large'))
                    : null,
                'ctaLabel' => $this->nullable($this->localized($post->ID, 'hero_cta_label', $locale)),
                'ctaUrl' => $this->meta($post->ID, 'hero_cta_url'),
            ],
            'about' => [
                'title' => $this->nullable($this->localized($post->ID, 'about_title', $locale)),
                'content' => $this->nullable($this->localized($post->ID, 'about_content', $locale)),
                'imageUrl' => $aboutImageId > 0
                    ? $this->absoluteUrl(wp_get_attachment_image_url($aboutImageId, 'large'))
                    : null,
            ],
            'services' => $services,
            'contact' => [
                'phone' => $this->meta($post->ID, 'phone'),
                'email' => $this->meta($post->ID, 'email'),
                'address' => $this->nullable($this->localized($post->ID, 'address', $locale)),
                'websiteUrl' => $this->meta($post->ID, 'website_url'),
            ],
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