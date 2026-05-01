<?php
declare(strict_types=1);

namespace DalmoaCore\Localization;

final class LocaleResolver
{
    public function resolve(?string $locale = null): string
    {
        $locale = is_string($locale) ? strtolower(trim($locale)) : 'ko';
        return in_array($locale, ['ko', 'en'], true) ? $locale : 'ko';
    }

    public function alternate(string $locale): string
    {
        return $locale === 'en' ? 'ko' : 'en';
    }
}