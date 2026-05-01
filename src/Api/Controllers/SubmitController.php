<?php
declare(strict_types=1);

namespace DalmoaCore\Api\Controllers;

use DalmoaCore\Support\Response;

final class SubmitController
{
    public function store(\WP_REST_Request $request): \WP_REST_Response
    {
        $type = (string) $request->get_param('type');
        $data = $request->get_params();
        $files = $request->get_file_params();

        $postId = wp_insert_post([
            'post_type' => $this->mapType($type),
            'post_title' => sanitize_text_field((string) ($data['title'] ?? '')),
            'post_content' => wp_kses_post((string) ($data['description'] ?? '')),
            'post_status' => 'publish',
        ]);

        if (!$postId || is_wp_error($postId)) {
            return Response::error('Failed to create');
        }

        $safeSlug = $type . '-' . $postId;

        wp_update_post([
            'ID' => $postId,
            'post_name' => $safeSlug,
        ]);

        $this->saveCommonMeta($postId, $data);
        $this->saveCategoryMeta($postId, $type, $data);

        $thumbnailId = null;
        $thumbnailUrl = null;

        if (!empty($files['image']) && !empty($files['image']['tmp_name'])) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachmentId = media_handle_upload('image', $postId);

            if (!is_wp_error($attachmentId)) {
                set_post_thumbnail($postId, $attachmentId);
                $thumbnailId = $attachmentId;
                $thumbnailUrl = wp_get_attachment_image_url($attachmentId, 'medium');
            }
        }

        return Response::json([
            'id' => $postId,
            'slug' => $safeSlug,
            'thumbnailId' => $thumbnailId,
            'thumbnailUrl' => $thumbnailUrl,
        ]);
    }

    private function saveCommonMeta(int $postId, array $data): void
    {
        update_post_meta($postId, 'region', sanitize_text_field((string) ($data['region'] ?? '')));
        update_post_meta($postId, 'price', $this->normalizeNumber($data['price'] ?? ''));
        update_post_meta($postId, 'is_featured', !empty($data['featured']) ? '1' : '0');
        update_post_meta($postId, 'contact_name', sanitize_text_field((string) ($data['contactName'] ?? '')));
        update_post_meta($postId, 'contact_phone', sanitize_text_field((string) ($data['contactPhone'] ?? '')));
        update_post_meta($postId, 'contact_email', sanitize_email((string) ($data['contactEmail'] ?? '')));
    }

    private function saveCategoryMeta(int $postId, string $type, array $data): void
    {
        $fields = match ($type) {
            'jobs' => [
                'companyName' => 'company_name',
                'salaryLabel' => 'salary_label',
                'jobType' => 'job_type',
            ],
            'business-sale' => [
                'businessCategory' => 'business_category',
                'salePriceLabel' => 'sale_price_label',
                'monthlyRevenueLabel' => 'monthly_revenue_label',
            ],
            'loan' => [
                'loanType' => 'loan_type',
                'interestRateLabel' => 'interest_rate_label',
                'loanAmountLabel' => 'loan_amount_label',
            ],
            'marketplace' => [
                'itemCondition' => 'item_condition',
                'priceLabel' => 'price_label',
            ],
            'real-estate' => [
                'propertyType' => 'property_type',
                'bedrooms' => 'bedrooms',
                'bathrooms' => 'bathrooms',
                'sizeLabel' => 'size_label',
            ],
            'cars' => [
                'carMake' => 'car_make',
                'carModel' => 'car_model',
                'carYear' => 'car_year',
                'mileageLabel' => 'mileage_label',
            ],
            default => [],
        };

        foreach ($fields as $inputKey => $metaKey) {
            $value = $data[$inputKey] ?? '';

            if (in_array($inputKey, ['bedrooms', 'bathrooms', 'carYear'], true)) {
                update_post_meta($postId, $metaKey, $this->normalizeNumber($value));
                continue;
            }

            update_post_meta($postId, $metaKey, sanitize_text_field((string) $value));
        }
    }

    private function normalizeNumber(mixed $value): string
    {
        return preg_replace('/[^\d]/', '', (string) $value) ?? '';
    }

    private function mapType(string $type): string
    {
        return match ($type) {
            'jobs' => 'job',
            'business-sale' => 'business_sale',
            'loan' => 'loan',
            'marketplace' => 'marketplace',
            'real-estate' => 'real_estate',
            'cars' => 'car',
            default => 'post',
        };
    }
}