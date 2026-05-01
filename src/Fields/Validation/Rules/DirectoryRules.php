<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Validation\Rules;

final class DirectoryRules
{
    public function validate(array $data): array
    {
        $errors = [];

        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $website = trim((string) ($data['website_url'] ?? ''));

        if ($phone === '' && $email === '' && $website === '') {
            $errors[] = '전화번호, 이메일, 웹사이트 URL 중 최소 1개는 입력해야 합니다.';
        }

        return $errors;
    }
}