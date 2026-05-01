<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Schemas;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class BusinessPageFieldSchema implements FieldSchemaInterface
{
    public static function postType(): string
    {
        return 'business_page';
    }

    public static function fields(): array
    {
        return [
            new FieldDefinition('hero_title_ko', FieldType::TEXT, '히어로 제목 (한국어)', required: true, translatable: true, adminSection: 'hero'),
            new FieldDefinition('hero_title_en', FieldType::TEXT, '히어로 제목 (영문)', translatable: true, adminSection: 'hero'),
            new FieldDefinition('hero_subtitle_ko', FieldType::TEXTAREA, '히어로 부제목 (한국어)', translatable: true, adminSection: 'hero'),
            new FieldDefinition('hero_subtitle_en', FieldType::TEXTAREA, '히어로 부제목 (영문)', translatable: true, adminSection: 'hero'),
            new FieldDefinition('hero_image_id', FieldType::IMAGE, '히어로 이미지', adminSection: 'hero'),
            new FieldDefinition('hero_cta_label_ko', FieldType::TEXT, '히어로 버튼 문구 (한국어)', adminSection: 'hero'),
            new FieldDefinition('hero_cta_label_en', FieldType::TEXT, '히어로 버튼 문구 (영문)', adminSection: 'hero'),
            new FieldDefinition('hero_cta_url', FieldType::URL, '히어로 버튼 URL', adminSection: 'hero'),

            new FieldDefinition('about_title_ko', FieldType::TEXT, '소개 제목 (한국어)', translatable: true, adminSection: 'about'),
            new FieldDefinition('about_title_en', FieldType::TEXT, '소개 제목 (영문)', translatable: true, adminSection: 'about'),
            new FieldDefinition('about_content_ko', FieldType::TEXTAREA, '소개 내용 (한국어)', translatable: true, adminSection: 'about'),
            new FieldDefinition('about_content_en', FieldType::TEXTAREA, '소개 내용 (영문)', translatable: true, adminSection: 'about'),
            new FieldDefinition('about_image_id', FieldType::IMAGE, '소개 이미지', adminSection: 'about'),

            new FieldDefinition('service_1_title_ko', FieldType::TEXT, '서비스 1 제목 (한국어)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_1_title_en', FieldType::TEXT, '서비스 1 제목 (영문)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_1_body_ko', FieldType::TEXTAREA, '서비스 1 설명 (한국어)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_1_body_en', FieldType::TEXTAREA, '서비스 1 설명 (영문)', translatable: true, adminSection: 'services'),

            new FieldDefinition('service_2_title_ko', FieldType::TEXT, '서비스 2 제목 (한국어)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_2_title_en', FieldType::TEXT, '서비스 2 제목 (영문)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_2_body_ko', FieldType::TEXTAREA, '서비스 2 설명 (한국어)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_2_body_en', FieldType::TEXTAREA, '서비스 2 설명 (영문)', translatable: true, adminSection: 'services'),

            new FieldDefinition('service_3_title_ko', FieldType::TEXT, '서비스 3 제목 (한국어)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_3_title_en', FieldType::TEXT, '서비스 3 제목 (영문)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_3_body_ko', FieldType::TEXTAREA, '서비스 3 설명 (한국어)', translatable: true, adminSection: 'services'),
            new FieldDefinition('service_3_body_en', FieldType::TEXTAREA, '서비스 3 설명 (영문)', translatable: true, adminSection: 'services'),

            new FieldDefinition('phone', FieldType::PHONE, '전화번호', adminSection: 'contact'),
            new FieldDefinition('email', FieldType::EMAIL, '이메일', adminSection: 'contact'),
            new FieldDefinition('website_url', FieldType::URL, '웹사이트 URL', adminSection: 'contact'),
            new FieldDefinition('address_ko', FieldType::TEXT, '주소 (한국어)', adminSection: 'contact'),
            new FieldDefinition('address_en', FieldType::TEXT, '주소 (영문)', adminSection: 'contact'),

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