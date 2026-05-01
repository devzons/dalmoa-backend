<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Schemas;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class MarketplaceFieldSchema implements FieldSchemaInterface
{
    public static function postType(): string
    {
        return 'marketplace';
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

            new FieldDefinition('price_label_ko', FieldType::TEXT, '가격 표시 (한국어)', adminSection: 'item'),
            new FieldDefinition('price_label_en', FieldType::TEXT, '가격 표시 (영문)', adminSection: 'item'),
            new FieldDefinition('item_condition', FieldType::SELECT, '상품 상태', adminSection: 'item', choices: [
                'new' => '새상품',
                'like_new' => '거의 새상품',
                'used' => '중고',
            ]),
            new FieldDefinition('item_location_ko', FieldType::TEXT, '거래 위치 (한국어)', adminSection: 'item'),
            new FieldDefinition('item_location_en', FieldType::TEXT, '거래 위치 (영문)', adminSection: 'item'),

            new FieldDefinition('contact_email', FieldType::EMAIL, '연락 이메일', adminSection: 'contact'),
            new FieldDefinition('contact_phone', FieldType::PHONE, '연락처', adminSection: 'contact'),
            new FieldDefinition('contact_url', FieldType::URL, '외부 링크', adminSection: 'contact'),

            new FieldDefinition('thumbnail_id', FieldType::IMAGE, '대표 이미지', adminSection: 'media'),

            new FieldDefinition('is_featured', FieldType::BOOLEAN, '추천 게시물', default: false, adminSection: 'status', publicApi: false),
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