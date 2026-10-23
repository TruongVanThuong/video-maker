<?php

declare(strict_types=1);

namespace App\Services\AI;

/**
 * Trung tâm quy định ngôn ngữ cho toàn bộ pipeline AI.
 *
 * - contentLanguage(): ngôn ngữ của nội dung AI tạo ra để hiển thị cho người dùng.
 * - providerPromptLanguage(): ngôn ngữ của prompt gửi tới model tạo video/hình ảnh.
 *
 * Mặc định là tiếng Việt. Có thể cấu hình tường minh sang tiếng Anh cho từng
 * tác vụ qua biến môi trường AI_CONTENT_LANGUAGE / AI_PROVIDER_PROMPT_LANGUAGE.
 * Không có chỗ nào tự động dịch nội dung người dùng sang ngôn ngữ khác.
 */
final class LanguagePolicy
{
    public const DEFAULT_CONTENT_LANGUAGE = 'vi';

    public const DEFAULT_PROVIDER_PROMPT_LANGUAGE = 'vi';

    public function contentLanguage(): string
    {
        return (string) config('services.ai.content_language', self::DEFAULT_CONTENT_LANGUAGE);
    }

    public function providerPromptLanguage(): string
    {
        return (string) config('services.ai.provider_prompt_language', self::DEFAULT_PROVIDER_PROMPT_LANGUAGE);
    }

    /**
     * Tên ngôn ngữ dùng trong instruction gửi cho model.
     */
    public function languageName(?string $code = null): string
    {
        return match (strtolower($code ?? $this->providerPromptLanguage())) {
            'vi', 'vi-vn' => 'Tiếng Việt',
            'en', 'en-us', 'en-gb' => 'Tiếng Anh',
            default => (string) ($code ?? 'Tiếng Việt'),
        };
    }

    public function outputLanguageRule(): string
    {
        $language = $this->languageName();

        return "Quy tắc ngôn ngữ bắt buộc: viết toàn bộ giá trị văn bản bằng {$language}. "
            . 'Không dịch và không thay đổi nội dung do người dùng cung cấp. '
            . 'Giữ nguyên tên riêng, thương hiệu, ID kỹ thuật, tên model, tham số API và cú pháp kỹ thuật. '
            . "Trường final_video_prompt phải là một prompt hoàn chỉnh, sẵn sàng cho API tạo video, bằng {$language}.";
    }

    /**
     * Ghép quy tắc ngôn ngữ vào một system instruction bất kỳ (kể cả template của người dùng)
     * để mọi tác vụ AI đều tuân theo cùng một chính sách ngôn ngữ.
     */
    public function withOutputLanguageRule(string $instruction): string
    {
        return rtrim($instruction) . "\n\n" . $this->outputLanguageRule();
    }

    /**
     * System instruction mặc định khi không có Prompt Template.
     */
    public function defaultSystemInstruction(): string
    {
        return $this->withOutputLanguageRule(
            'Bạn là một chuyên gia Prompt Engineer cho video AI. '
            . 'Phân tích nội dung của người dùng và tạo ra các tham số có cấu trúc '
            . 'cho các công cụ tạo video bằng AI (Sora/Runway/Kling).'
        );
    }
}
