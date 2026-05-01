<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Schemas;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class AdListingFieldSchema implements FieldSchemaInterface
{
    public static function postType(): string
    {
        return 'ad_listing';
    }

    public static function fields(): array
    {
        return [
            new FieldDefinition('title_ko', FieldType::TEXT, '광고 제목 (한국어)', required: true, translatable: true, adminSection: 'content'),
            new FieldDefinition('title_en', FieldType::TEXT, '광고 제목 (영문)', default: '', translatable: true, adminSection: 'content'),
            new FieldDefinition('excerpt_ko', FieldType::TEXTAREA, '광고 요약 (한국어)', default: '', translatable: true, adminSection: 'content'),
            new FieldDefinition('excerpt_en', FieldType::TEXTAREA, '광고 요약 (영문)', default: '', translatable: true, adminSection: 'content'),

            new FieldDefinition('cta_label_ko', FieldType::TEXT, '버튼 문구 (한국어)', default: '', adminSection: 'cta'),
            new FieldDefinition('cta_label_en', FieldType::TEXT, '버튼 문구 (영문)', default: '', adminSection: 'cta'),
            new FieldDefinition('cta_url', FieldType::URL, '버튼 URL', default: '', adminSection: 'cta'),
            new FieldDefinition('target_external', FieldType::BOOLEAN, '외부 링크', default: false, adminSection: 'cta'),

            new FieldDefinition('thumbnail_id', FieldType::IMAGE, '대표 이미지', default: 0, adminSection: 'media'),
            new FieldDefinition('region_key', FieldType::TEXT, '지역 키', default: '', adminSection: 'targeting'),

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
            new FieldDefinition('is_paid', FieldType::BOOLEAN, '유료 광고', default: false, adminSection: 'promotion', publicApi: false),
            new FieldDefinition('is_featured', FieldType::BOOLEAN, '추천 광고', default: false, adminSection: 'promotion', publicApi: false),
            new FieldDefinition('priority_score', FieldType::NUMBER, '우선순위 점수', default: 0, adminSection: 'promotion', publicApi: false),
            new FieldDefinition('ad_starts_at', FieldType::TEXT, '광고 시작일시 (Y-m-d H:i:s)', default: '', adminSection: 'promotion', publicApi: false),
            new FieldDefinition('ad_ends_at', FieldType::TEXT, '광고 종료일시 (Y-m-d H:i:s)', default: '', adminSection: 'promotion', publicApi: false),
            new FieldDefinition('expires_at', FieldType::TEXT, '만료일시 (Y-m-d H:i:s)', default: '', adminSection: 'promotion', publicApi: false),
            new FieldDefinition('payment_status', FieldType::TEXT, '결제 상태', default: 'none', adminSection: 'promotion', publicApi: false),

            new FieldDefinition('stripe_session_id', FieldType::TEXT, 'Stripe Session ID', default: '', adminSection: 'payment', publicApi: false),
            new FieldDefinition('stripe_payment_intent_id', FieldType::TEXT, 'Stripe Payment Intent ID', default: '', adminSection: 'payment', publicApi: false),
            new FieldDefinition('stripe_customer_id', FieldType::TEXT, 'Stripe Customer ID', default: '', adminSection: 'payment', publicApi: false),
            new FieldDefinition('paid_at', FieldType::TEXT, '결제 완료일시 (Y-m-d H:i:s)', default: '', adminSection: 'payment', publicApi: false),

            new FieldDefinition(
                'billing_type',
                FieldType::SELECT,
                '결제 방식',
                default: 'one_time',
                adminSection: 'payment',
                publicApi: false,
                choices: [
                    'one_time' => 'One-time',
                    'subscription' => 'Subscription',
                ]
            ),
            new FieldDefinition('stripe_subscription_id', FieldType::TEXT, 'Stripe Subscription ID', default: '', adminSection: 'payment', publicApi: false),
            new FieldDefinition('subscription_status', FieldType::TEXT, '구독 상태', default: 'none', adminSection: 'payment', publicApi: false),
            new FieldDefinition('subscription_started_at', FieldType::TEXT, '구독 시작일시', default: '', adminSection: 'payment', publicApi: false),
            new FieldDefinition('subscription_cancelled_at', FieldType::TEXT, '구독 취소일시', default: '', adminSection: 'payment', publicApi: false),

            new FieldDefinition('click_count', FieldType::NUMBER, '클릭 수', default: 0, adminSection: 'analytics', publicApi: false),
            new FieldDefinition('impression_count', FieldType::NUMBER, '노출 수', default: 0, adminSection: 'analytics', publicApi: false),

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

            new FieldDefinition('ab_enabled', FieldType::BOOLEAN, 'A/B 테스트 활성화', default: false, adminSection: 'ab_test', publicApi: false),
            new FieldDefinition(
                'ab_strategy',
                FieldType::SELECT,
                'A/B 전략',
                default: 'weighted',
                adminSection: 'ab_test',
                publicApi: false,
                choices: [
                    'weighted' => 'Weighted',
                    'auto_ctr' => 'Auto CTR',
                ]
            ),
            new FieldDefinition(
                'ab_variants',
                FieldType::TEXTAREA,
                'A/B Variants (JSON)',
                default: '',
                adminSection: 'ab_test',
                publicApi: false
            ),
        ];
    }
}