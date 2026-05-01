<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Schemas;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class CarFieldSchema implements FieldSchemaInterface
{
    public static function postType(): string
    {
        return 'car';
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

            new FieldDefinition('price_label_ko', FieldType::TEXT, '가격 표시 (한국어)', adminSection: 'vehicle'),
            new FieldDefinition('price_label_en', FieldType::TEXT, '가격 표시 (영문)', adminSection: 'vehicle'),
            new FieldDefinition('car_year', FieldType::NUMBER, '연식', adminSection: 'vehicle'),
            new FieldDefinition('car_make', FieldType::TEXT, '제조사', adminSection: 'vehicle'),
            new FieldDefinition('car_model', FieldType::TEXT, '모델', adminSection: 'vehicle'),
            new FieldDefinition('mileage_label_ko', FieldType::TEXT, '주행거리 표시 (한국어)', adminSection: 'vehicle'),
            new FieldDefinition('mileage_label_en', FieldType::TEXT, '주행거리 표시 (영문)', adminSection: 'vehicle'),
            new FieldDefinition('car_location_ko', FieldType::TEXT, '위치 (한국어)', adminSection: 'vehicle'),
            new FieldDefinition('car_location_en', FieldType::TEXT, '위치 (영문)', adminSection: 'vehicle'),

            new FieldDefinition('contact_email', FieldType::EMAIL, '연락 이메일', adminSection: 'contact'),
            new FieldDefinition('contact_phone', FieldType::PHONE, '연락처', adminSection: 'contact'),
            new FieldDefinition('contact_url', FieldType::URL, '외부 링크', adminSection: 'contact'),

            new FieldDefinition('thumbnail_id', FieldType::IMAGE, '대표 이미지', adminSection: 'media'),

            new FieldDefinition('is_featured', FieldType::BOOLEAN, '추천 차량', default: false, adminSection: 'status', publicApi: false),
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