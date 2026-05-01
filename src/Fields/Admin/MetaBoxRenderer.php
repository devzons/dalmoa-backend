<?php
declare(strict_types=1);

namespace DalmoaCore\Fields\Admin;

use DalmoaCore\Fields\Contracts\FieldSchemaInterface;
use DalmoaCore\Fields\Support\FieldDefinition;
use DalmoaCore\Fields\Support\FieldType;

final class MetaBoxRenderer
{
    public function render(\WP_Post $post, string $schemaClass): void
    {
        if (!is_subclass_of($schemaClass, FieldSchemaInterface::class)) {
            return;
        }

        $fields = $schemaClass::fields();
        $sections = $this->groupFieldsBySection($fields);

        wp_nonce_field('dalmoa_meta_box', 'dalmoa_meta_box_nonce');

        echo '<div class="dalmoa-admin">';
        echo '<style>
            .dalmoa-admin{padding:8px 0;}
            .dalmoa-section{margin:0 0 28px;}
            .dalmoa-section-title{margin:0 0 14px;font-size:16px;font-weight:700;padding-bottom:8px;border-bottom:1px solid #e5e7eb;}
            .dalmoa-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
            .dalmoa-field{display:flex;flex-direction:column;gap:6px;}
            .dalmoa-field-full{grid-column:1 / -1;}
            .dalmoa-label{font-weight:600;}
            .dalmoa-required{color:#dc2626;margin-left:4px;}
            .dalmoa-help{font-size:12px;color:#6b7280;line-height:1.5;}
            .dalmoa-thumb-preview img{max-width:220px;height:auto;border-radius:8px;border:1px solid #e5e7eb;}
            .dalmoa-input,
            .dalmoa-select,
            .dalmoa-textarea{width:100%;}
            .dalmoa-image-actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap;}
            .dalmoa-hidden{display:none;}
        </style>';

        foreach ($sections as $section => $sectionFields) {
            echo '<div class="dalmoa-section">';
            echo '<h3 class="dalmoa-section-title">' . esc_html($this->sectionTitle($section)) . '</h3>';
            echo '<div class="dalmoa-grid">';

            foreach ($sectionFields as $field) {
                $this->renderField($post->ID, $field);
            }

            echo '</div>';
            echo '</div>';
        }

        echo '</div>';

        $this->renderMediaScript();
    }

    /**
     * @param FieldDefinition[] $fields
     * @return array<string, FieldDefinition[]>
     */
    private function groupFieldsBySection(array $fields): array
    {
        $grouped = [];

        foreach ($fields as $field) {
            $grouped[$field->adminSection][] = $field;
        }

        return $grouped;
    }

    private function renderField(int $postId, FieldDefinition $field): void
    {
        $value = get_post_meta($postId, $field->key, true);

        if ($value === '' || $value === null) {
            $value = $field->default;
        }

        $fullWidth = in_array($field->type, [FieldType::TEXTAREA, FieldType::IMAGE], true);
        $fieldClass = $fullWidth ? 'dalmoa-field dalmoa-field-full' : 'dalmoa-field';

        echo '<div class="' . esc_attr($fieldClass) . '">';
        echo '<label class="dalmoa-label" for="' . esc_attr($field->key) . '">';
        echo esc_html($field->label);

        if ($field->required) {
            echo '<span class="dalmoa-required">*</span>';
        }

        echo '</label>';

        switch ($field->type) {
            case FieldType::TEXT:
            case FieldType::EMAIL:
            case FieldType::URL:
            case FieldType::PHONE:
                printf(
                    '<input class="regular-text dalmoa-input" type="text" id="%1$s" name="%1$s" value="%2$s" placeholder="%3$s" %4$s />',
                    esc_attr($field->key),
                    esc_attr((string) $value),
                    esc_attr((string) ($field->placeholder ?? '')),
                    $field->required ? 'required' : ''
                );
                break;

            case FieldType::NUMBER:
                printf(
                    '<input class="small-text dalmoa-input" type="number" id="%1$s" name="%1$s" value="%2$s" min="%3$s" max="%4$s" step="%5$s" %6$s />',
                    esc_attr($field->key),
                    esc_attr((string) $value),
                    esc_attr($field->min !== null ? (string) $field->min : ''),
                    esc_attr($field->max !== null ? (string) $field->max : ''),
                    esc_attr($field->step ?? '1'),
                    $field->required ? 'required' : ''
                );
                break;

            case FieldType::BOOLEAN:
                printf(
                    '<label><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s /> 사용</label>',
                    esc_attr($field->key),
                    checked((bool) $value, true, false)
                );
                break;

            case FieldType::SELECT:
                echo '<select class="dalmoa-select" id="' . esc_attr($field->key) . '" name="' . esc_attr($field->key) . '">';
                echo '<option value="">선택</option>';

                foreach ($field->choices as $choiceValue => $choiceLabel) {
                    echo '<option value="' . esc_attr((string) $choiceValue) . '" ' . selected((string) $value, (string) $choiceValue, false) . '>';
                    echo esc_html((string) $choiceLabel);
                    echo '</option>';
                }

                echo '</select>';
                break;

            case FieldType::TEXTAREA:
                printf(
                    '<textarea class="large-text dalmoa-textarea" id="%1$s" name="%1$s" rows="%2$d" placeholder="%3$s" %4$s>%5$s</textarea>',
                    esc_attr($field->key),
                    $field->rows,
                    esc_attr((string) ($field->placeholder ?? '')),
                    $field->required ? 'required' : '',
                    esc_textarea((string) $value)
                );
                break;

            case FieldType::IMAGE:
                $attachmentId = (int) $value;
                $imageUrl = $attachmentId > 0 ? wp_get_attachment_image_url($attachmentId, 'medium') : '';

                echo '<input type="hidden" id="' . esc_attr($field->key) . '" name="' . esc_attr($field->key) . '" value="' . esc_attr((string) $attachmentId) . '" class="dalmoa-media-input" />';

                echo '<div class="dalmoa-image-actions">';
                echo '<button type="button" class="button dalmoa-media-select" data-target="' . esc_attr($field->key) . '">이미지 선택</button>';
                echo '<button type="button" class="button dalmoa-media-remove" data-target="' . esc_attr($field->key) . '">이미지 제거</button>';

                if ($attachmentId > 0) {
                    echo '<span>Attachment ID: <strong class="dalmoa-media-id-text" data-target="' . esc_attr($field->key) . '">' . esc_html((string) $attachmentId) . '</strong></span>';
                } else {
                    echo '<span>Attachment ID: <strong class="dalmoa-media-id-text" data-target="' . esc_attr($field->key) . '">없음</strong></span>';
                }

                echo '</div>';

                echo '<div class="dalmoa-thumb-preview ' . ($imageUrl ? '' : 'dalmoa-hidden') . '" id="' . esc_attr($field->key) . '_preview" style="margin-top:10px;">';

                if ($imageUrl) {
                    echo '<img src="' . esc_url($imageUrl) . '" alt="" />';
                } else {
                    echo '<img src="" alt="" class="dalmoa-hidden" />';
                }

                echo '</div>';
                break;
        }

        if ($field->helpText) {
            echo '<div class="dalmoa-help">' . esc_html($field->helpText) . '</div>';
        }

        echo '</div>';
    }

    private function sectionTitle(string $section): string
    {
        return match ($section) {
            'content' => '콘텐츠',
            'company' => '회사 정보',
            'property' => '매물 정보',
            'vehicle' => '차량 정보',
            'item' => '상품 정보',
            'contact' => '연락처',
            'media' => '미디어',
            'meta' => '추가 정보',
            'status' => '상태 관리',
            default => '기본 정보',
        };
    }

    private function renderMediaScript(): void
    {
        echo '<script>
            (function(){
                if (typeof wp === "undefined" || typeof wp.media === "undefined") {
                    return;
                }

                let frame = null;

                function updatePreview(target, attachment) {
                    const input = document.getElementById(target);
                    const preview = document.getElementById(target + "_preview");
                    const previewImage = preview ? preview.querySelector("img") : null;
                    const idText = document.querySelector(\'[data-target="\' + target + \'"].dalmoa-media-id-text\');

                    if (!input || !preview || !previewImage) {
                        return;
                    }

                    if (!attachment) {
                        input.value = "";
                        previewImage.src = "";
                        previewImage.classList.add("dalmoa-hidden");
                        preview.classList.add("dalmoa-hidden");

                        if (idText) {
                            idText.textContent = "없음";
                        }

                        return;
                    }

                    input.value = attachment.id || "";
                    previewImage.src = attachment.url || "";
                    previewImage.classList.remove("dalmoa-hidden");
                    preview.classList.remove("dalmoa-hidden");

                    if (idText) {
                        idText.textContent = String(attachment.id || "");
                    }
                }

                document.addEventListener("click", function(event){
                    const selectButton = event.target.closest(".dalmoa-media-select");
                    const removeButton = event.target.closest(".dalmoa-media-remove");

                    if (selectButton) {
                        const target = selectButton.getAttribute("data-target");
                        if (!target) {
                            return;
                        }

                        frame = wp.media({
                            title: "이미지 선택",
                            button: {
                                text: "사용하기"
                            },
                            multiple: false,
                            library: {
                                type: "image"
                            }
                        });

                        frame.on("select", function(){
                            const attachment = frame.state().get("selection").first().toJSON();
                            updatePreview(target, attachment);
                        });

                        frame.open();
                    }

                    if (removeButton) {
                        const target = removeButton.getAttribute("data-target");
                        if (!target) {
                            return;
                        }

                        updatePreview(target, null);
                    }
                });
            })();
        </script>';
    }
}