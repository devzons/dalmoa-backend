<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Schemas;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class JobFieldSchema implements FieldSchemaInterface
{
    public static function postType(): string
    {
        return 'job';
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

            new FieldDefinition('company_name', FieldType::TEXT, '회사명', required: true, adminSection: 'company'),
            new FieldDefinition('job_location_ko', FieldType::TEXT, '근무지 (한국어)', adminSection: 'company'),
            new FieldDefinition('job_location_en', FieldType::TEXT, '근무지 (영문)', adminSection: 'company'),
            new FieldDefinition('employment_type', FieldType::SELECT, '고용 형태', adminSection: 'company', choices: [
                'full_time' => '정규직',
                'part_time' => '파트타임',
                'contract' => '계약직',
                'temporary' => '임시직',
            ]),
            new FieldDefinition('salary_label_ko', FieldType::TEXT, '급여 표시 (한국어)', adminSection: 'company'),
            new FieldDefinition('salary_label_en', FieldType::TEXT, '급여 표시 (영문)', adminSection: 'company'),

            new FieldDefinition('contact_email', FieldType::EMAIL, '지원 이메일', adminSection: 'contact'),
            new FieldDefinition('contact_phone', FieldType::PHONE, '연락처', adminSection: 'contact'),
            new FieldDefinition('apply_url', FieldType::URL, '지원 URL', adminSection: 'contact'),

            new FieldDefinition('thumbnail_id', FieldType::IMAGE, '대표 이미지', adminSection: 'media'),

            new FieldDefinition('is_featured', FieldType::BOOLEAN, '추천 채용', default: false, adminSection: 'status', publicApi: false),
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