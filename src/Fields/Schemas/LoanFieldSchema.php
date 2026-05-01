<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Schemas;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class LoanFieldSchema implements FieldSchemaInterface
{
    public static function postType(): string
    {
        return 'loan';
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

            new FieldDefinition('loan_type', FieldType::SELECT, '융자 유형', adminSection: 'loan', choices: [
                'mortgage' => '주택융자',
                'business' => '사업융자',
                'commercial' => '상업용융자',
                'auto' => '자동차융자',
                'bridge' => '브리지융자',
            ]),
            new FieldDefinition('interest_rate_label_ko', FieldType::TEXT, '이자율 표시 (한국어)', adminSection: 'loan'),
            new FieldDefinition('interest_rate_label_en', FieldType::TEXT, '이자율 표시 (영문)', adminSection: 'loan'),
            new FieldDefinition('loan_amount_label_ko', FieldType::TEXT, '융자 한도 표시 (한국어)', adminSection: 'loan'),
            new FieldDefinition('loan_amount_label_en', FieldType::TEXT, '융자 한도 표시 (영문)', adminSection: 'loan'),
            new FieldDefinition('location_ko', FieldType::TEXT, '위치 (한국어)', adminSection: 'loan'),
            new FieldDefinition('location_en', FieldType::TEXT, '위치 (영문)', adminSection: 'loan'),

            new FieldDefinition('contact_email', FieldType::EMAIL, '연락 이메일', adminSection: 'contact'),
            new FieldDefinition('contact_phone', FieldType::PHONE, '연락처', adminSection: 'contact'),
            new FieldDefinition('contact_url', FieldType::URL, '외부 링크', adminSection: 'contact'),

            new FieldDefinition('thumbnail_id', FieldType::IMAGE, '대표 이미지', adminSection: 'media'),

            new FieldDefinition('is_featured', FieldType::BOOLEAN, '추천 상품', default: false, adminSection: 'status', publicApi: false),
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