<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Schemas;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class DirectoryFieldSchema implements FieldSchemaInterface
{
    public static function postType(): string
    {
        return 'directory';
    }

    public static function fields(): array
    {
        return [
            new FieldDefinition('title_ko', FieldType::TEXT, '업소명 (한국어)', required: true, translatable: true, adminSection: 'content'),
            new FieldDefinition('title_en', FieldType::TEXT, '업소명 (영문)', translatable: true, adminSection: 'content'),
            new FieldDefinition('excerpt_ko', FieldType::TEXTAREA, '요약 (한국어)', translatable: true, adminSection: 'content'),
            new FieldDefinition('excerpt_en', FieldType::TEXTAREA, '요약 (영문)', translatable: true, adminSection: 'content'),
            new FieldDefinition('content_ko', FieldType::TEXTAREA, '상세 소개 (한국어)', translatable: true, adminSection: 'content'),
            new FieldDefinition('content_en', FieldType::TEXTAREA, '상세 소개 (영문)', translatable: true, adminSection: 'content'),

            new FieldDefinition(
                'business_category',
                FieldType::SELECT,
                '업종',
                required: true,
                adminSection: 'business',
                choices: [
                    'korean_restaurant' => '한식',
                    'chinese_restaurant' => '중식',
                    'japanese_restaurant' => '일식',
                    'asian_cuisine' => '아시안',
                    'fast_food' => '패스트푸드',
                    'cafe_dessert' => '카페/디저트',
                    'bakery' => '베이커리',
                    'bar_pub' => '바/주점',

                    'hair_salon' => '미용실',
                    'nail_eyelash' => '네일/속눈썹',
                    'spa_massage' => '스파/마사지',
                    'skin_care' => '피부관리',
                    'cosmetic_clinic' => '성형/뷰티클리닉',

                    'medical_clinic' => '병원',
                    'dental_clinic' => '치과',
                    'acupuncture_chiropractic' => '한의원/척추',
                    'pharmacy' => '약국',
                    'mental_health' => '정신건강',
                    'physical_therapy' => '물리치료',

                    'attorney' => '변호사',
                    'immigration_attorney' => '이민변호사',
                    'cpa_tax' => '회계/세무',
                    'consulting' => '컨설팅',
                    'notary' => '공증',

                    'real_estate' => '부동산',
                    'rental_leasing' => '렌트/임대',
                    'property_management' => '부동산 관리',
                    'loan_mortgage' => '융자/모기지',
                    'insurance' => '보험',
                    'finance_banking' => '금융/은행',

                    'car_dealership' => '자동차 판매',
                    'used_cars' => '중고차',
                    'auto_repair' => '정비소',
                    'body_shop' => '바디샵',
                    'car_wash_detailing' => '세차/디테일링',
                    'car_rental' => '렌터카',

                    'moving' => '이사',
                    'delivery' => '택배/배송',
                    'storage' => '창고/보관',
                    'cleaning' => '청소',
                    'home_services' => '가정서비스',

                    'construction' => '건축',
                    'remodeling' => '리모델링',
                    'electrical' => '전기',
                    'plumbing' => '배관',
                    'hvac' => '에어컨/HVAC',
                    'roofing' => '지붕/루핑',
                    'interior_design' => '인테리어',

                    'academy' => '학원',
                    'daycare' => '유치원/어린이집',
                    'tutoring' => '과외/튜터',
                    'driving_school' => '운전학원',
                    'music_art' => '음악/미술',

                    'church' => '교회',
                    'religious_org' => '성당/사찰',

                    'travel_agency' => '여행사',
                    'air_tickets' => '항공권',
                    'hotel_lodging' => '호텔/숙박',
                    'tour_activity' => '투어',

                    'grocery_store' => '마트',
                    'meat_seafood' => '정육/생선',
                    'health_food' => '건강식품',
                    'liquor_store' => '주류',

                    'it_services' => 'IT 서비스',
                    'web_development' => '웹 개발',
                    'marketing' => '마케팅',
                    'advertising' => '광고',
                    'design' => '디자인',
                    'printing' => '프린팅',

                    'photography' => '사진',
                    'video_production' => '영상 제작',

                    'event_wedding' => '이벤트/웨딩',
                    'florist' => '꽃집',
                    'gift_shop' => '선물',

                    'gym_fitness' => '헬스장',
                    'yoga_pilates' => '요가/필라테스',
                    'sports' => '스포츠',

                    'pet_services' => '애완동물',
                    'vet_clinic' => '동물병원',

                    'laundry' => '세탁소',
                    'alteration' => '수선',
                    'locksmith' => '열쇠',
                    'local_services' => '생활서비스',
                    'other' => '기타',
                ]
            ),
            new FieldDefinition('phone', FieldType::PHONE, '전화번호', adminSection: 'contact'),
            new FieldDefinition('email', FieldType::EMAIL, '이메일', adminSection: 'contact'),
            new FieldDefinition('website_url', FieldType::URL, '웹사이트 URL', adminSection: 'contact'),

            new FieldDefinition('address_ko', FieldType::TEXT, '주소 (한국어)', adminSection: 'location'),
            new FieldDefinition('address_en', FieldType::TEXT, '주소 (영문)', adminSection: 'location'),
            new FieldDefinition('thumbnail_id', FieldType::IMAGE, '대표 이미지', adminSection: 'media'),

            new FieldDefinition('is_featured', FieldType::BOOLEAN, '추천 업소', default: false, adminSection: 'promotion', publicApi: false),
            new FieldDefinition(
                'ad_plan',
                FieldType::SELECT,
                '광고 상품',
                default: 'basic',
                adminSection: 'promotion',
                publicApi: false,
                choices: [
                    'basic' => 'Basic',
                    'featured' => 'Featured',
                    'premium' => 'Premium',
                ]
            ),
            new FieldDefinition('ad_starts_at', FieldType::TEXT, '광고 시작일', adminSection: 'promotion', publicApi: false),
            new FieldDefinition('ad_ends_at', FieldType::TEXT, '광고 종료일', adminSection: 'promotion', publicApi: false),

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