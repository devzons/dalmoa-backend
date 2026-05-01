<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Persistence;

use DalmoaCore\Fields\Validation\Rules\AdRules;
use DalmoaCore\Fields\Validation\Rules\DirectoryRules;
use DalmoaCore\Fields\Validation\Sanitizer;
use DalmoaCore\Fields\Validation\Validator;

final class MetaSaver
{
    public function __construct(
        private readonly Sanitizer $sanitizer = new Sanitizer(),
        private readonly Validator $validator = new Validator(),
        private readonly DirectoryRules $directoryRules = new DirectoryRules(),
        private readonly AdRules $adRules = new AdRules(),
    ) {}

    /**
     * @param class-string $schemaClass
     */
    public function save(int $postId, string $schemaClass): void
    {
        if (!$this->canSave($postId)) {
            return;
        }

        $rawPost = wp_unslash($_POST);
        $errors = [];
        $cleanData = [];

        foreach ($schemaClass::fields() as $field) {
            $rawValue = $rawPost[$field->key] ?? $field->default;

            if ($field->type === 'boolean') {
                $rawValue = isset($rawPost[$field->key]) ? '1' : '0';
            }

            $sanitized = $this->sanitizer->sanitize($field, $rawValue);
            $fieldErrors = $this->validator->validate($field, $sanitized);

            if ($fieldErrors !== []) {
                array_push($errors, ...$fieldErrors);
            }

            $cleanData[$field->key] = $sanitized;
        }

        $postType = $schemaClass::postType();

        if ($postType === 'directory') {
            array_push($errors, ...$this->directoryRules->validate($cleanData));
        }

        if ($postType === 'ad_listing') {
            array_push($errors, ...$this->adRules->validate($cleanData));
        }

        if ($errors !== []) {
            update_post_meta($postId, '_dalmoa_admin_errors', $errors);
        } else {
            delete_post_meta($postId, '_dalmoa_admin_errors');
        }

        foreach ($schemaClass::fields() as $field) {
            $value = $cleanData[$field->key] ?? $field->default;

            if ($field->type === 'boolean') {
                update_post_meta($postId, $field->key, $value ? '1' : '0');
                continue;
            }

            if ($value === null || $value === '') {
                delete_post_meta($postId, $field->key);
                continue;
            }

            update_post_meta($postId, $field->key, $value);
        }

        delete_post_meta($postId, '_dalmoa_admin_errors');
    }

    private function canSave(int $postId): bool
    {
        if (!isset($_POST['dalmoa_meta_box_nonce'])) {
            return false;
        }

        $nonce = sanitize_text_field(wp_unslash($_POST['dalmoa_meta_box_nonce']));

        if (!wp_verify_nonce($nonce, 'dalmoa_meta_box')) {
            return false;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (wp_is_post_autosave($postId) || wp_is_post_revision($postId)) {
            return false;
        }

        if (!current_user_can('edit_post', $postId)) {
            return false;
        }

        return true;
    }
}