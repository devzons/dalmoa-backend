<?php
declare(strict_types=1);

namespace DalmoaCore\Support\Labels;

final class DisplayLabel
{
    public static function moderationStatus(string $value): string
    {
        return match ($value) {
            'approved' => '승인',
            'pending' => '대기',
            'rejected' => '반려',
            default => $value,
        };
    }

    public static function employmentType(string $value, string $locale = 'ko'): string
    {
        if ($locale === 'en') {
            return match ($value) {
                'full_time' => 'Full-time',
                'part_time' => 'Part-time',
                'contract' => 'Contract',
                'temporary' => 'Temporary',
                default => $value,
            };
        }

        return match ($value) {
            'full_time' => '정규직',
            'part_time' => '파트타임',
            'contract' => '계약직',
            'temporary' => '임시직',
            default => $value,
        };
    }

    public static function listingType(string $value, string $locale = 'ko'): string
    {
        if ($locale === 'en') {
            return match ($value) {
                'sale' => 'For Sale',
                'rent' => 'For Rent',
                'lease' => 'For Lease',
                default => $value,
            };
        }

        return match ($value) {
            'sale' => '매매',
            'rent' => '렌트',
            'lease' => '리스',
            default => $value,
        };
    }

    public static function propertyType(string $value, string $locale = 'ko'): string
    {
        if ($locale === 'en') {
            return match ($value) {
                'house' => 'House',
                'apartment' => 'Apartment',
                'condo' => 'Condo',
                'commercial' => 'Commercial',
                'land' => 'Land',
                default => $value,
            };
        }

        return match ($value) {
            'house' => '하우스',
            'apartment' => '아파트',
            'condo' => '콘도',
            'commercial' => '상업용',
            'land' => '토지',
            default => $value,
        };
    }

    public static function itemCondition(string $value, string $locale = 'ko'): string
    {
        if ($locale === 'en') {
            return match ($value) {
                'new' => 'New',
                'like_new' => 'Like New',
                'used' => 'Used',
                default => $value,
            };
        }

        return match ($value) {
            'new' => '새상품',
            'like_new' => '거의 새상품',
            'used' => '중고',
            default => $value,
        };
    }
}