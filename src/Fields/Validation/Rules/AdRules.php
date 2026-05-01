<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Validation\Rules;

final class AdRules
{
    public function validate(array $data): array
    {
        $errors = [];

        $ctaUrl = trim((string) ($data['cta_url'] ?? ''));
        $isPaid = !empty($data['is_paid']);
        $thumbnailId = (int) ($data['thumbnail_id'] ?? 0);

        if ($ctaUrl === '') {
            $errors[] = '광고는 CTA URL이 필요합니다.';
        }

        if ($isPaid && $thumbnailId <= 0) {
            $errors[] = '유료 광고는 대표 이미지가 필요합니다.';
        }

        return $errors;
    }
}