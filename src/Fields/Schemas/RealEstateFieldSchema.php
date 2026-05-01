<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Schemas;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class RealEstateFieldSchema implements FieldSchemaInterface
{
    public static function postType(): string
    {
        return 'real_estate';
    }

    public static function fields(): array
    {
        return [
            new FieldDefinition('title_ko', FieldType::TEXT, '제목 (한국어)', required: true, translatable: true, adminSection: 'content'),
            new FieldDefinition('title_en', FieldType::TEXT, '제목 (영문)', translatable: true, adminSection: 'content'),
            new FieldDefinition('excerpt_ko', FieldType::TEXTAREA, '요약 (한국어)', translatable: true, adminSection: 'content'),
            new FieldDefinition('excerpt_en', FieldType::TEXTAREA, '요약 (영문)', translatable: true, adminSection: 'content'),
            new FieldDefinition('content_ko', FieldType::TEXTAREA, '본문 (한국어)', translatable: true, adminSection: 'content'),
            new FieldDefinition('content_en', FieldType::TEXTAREA, '본문 (영문)', translatable: true, adminSection: 'content'),

            new FieldDefinition('listing_type', FieldType::SELECT, '매물 유형', adminSection: 'property', choices: [
                'sale' => '매매',
                'rent' => '렌트',
                'lease' => '리스',
            ]),
            new FieldDefinition('property_type', FieldType::SELECT, '부동산 유형', adminSection: 'property', choices: [
                'house' => '하우스',
                'apartment' => '아파트',
                'condo' => '콘도',
                'commercial' => '상업용',
                'land' => '토지',
            ]),
            new FieldDefinition('price_label_ko', FieldType::TEXT, '가격 표시 (한국어)', adminSection: 'property'),
            new FieldDefinition('price_label_en', FieldType::TEXT, '가격 표시 (영문)', adminSection: 'property'),
            new FieldDefinition('bedrooms', FieldType::NUMBER, '침실 수', adminSection: 'property'),
            new FieldDefinition('bathrooms', FieldType::NUMBER, '욕실 수', adminSection: 'property'),
            new FieldDefinition('property_location_ko', FieldType::TEXT, '위치 (한국어)', adminSection: 'property'),
            new FieldDefinition('property_location_en', FieldType::TEXT, '위치 (영문)', adminSection: 'property'),

            new FieldDefinition('contact_email', FieldType::EMAIL, '연락 이메일', adminSection: 'contact'),
            new FieldDefinition('contact_phone', FieldType::PHONE, '연락처', adminSection: 'contact'),
            new FieldDefinition('contact_url', FieldType::URL, '외부 링크', adminSection: 'contact'),

            new FieldDefinition('thumbnail_id', FieldType::IMAGE, '대표 이미지', adminSection: 'media'),

            new FieldDefinition('is_featured', FieldType::BOOLEAN, '추천 매물', default: false, adminSection: 'status', publicApi: false),
            new FieldDefinition('is_active', FieldType::BOOLEAN, '활성 상태', default: true, adminSection: 'status', publicApi: false),
            new FieldDefinition(
                'moderation_status',
                FieldType::SELECT,
                '검수 상태',
                default: 'approved',
                adminSection: 'status',
                publicApi: false,
                choices: [
                    'approved' => '승인',
                    'pending' => '대기',
                    'rejected' => '반려',
                ]
            ),
        ];
    }
}