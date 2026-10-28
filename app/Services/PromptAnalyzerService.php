<?php

namespace App\Services;

use App\Models\ContentPrompt;
use App\Services\AI\LanguagePolicy;
use RuntimeException;

class PromptAnalyzerService
{
    private GeminiApiClient $geminiClient;

    private LanguagePolicy $languagePolicy;

    public function __construct(GeminiApiClient $geminiClient, LanguagePolicy $languagePolicy)
    {
        $this->geminiClient = $geminiClient;
        $this->languagePolicy = $languagePolicy;
    }

    public function analyze(ContentPrompt $contentPrompt): array
    {
        $template = $contentPrompt->template;

        $systemInstruction = $this->buildSystemInstruction($template?->system_instruction);

        $userPrompt = $this->buildUserPrompt($contentPrompt->input_content, $contentPrompt->target_platform);
        $jsonSchema = $this->getVideoPromptSchema();

        $data = $this->geminiClient->generateContent(
            $systemInstruction,
            $userPrompt,
            $jsonSchema
        );

        if (!is_array($data) || empty($data['final_video_prompt'])) {
            throw new RuntimeException('Gemini trả về phản hồi không hợp lệ hoặc thiếu dữ liệu.');
        }

        return $data;
    }

    private function buildSystemInstruction(?string $templateInstruction): string
    {
        if (empty($templateInstruction)) {
            return $this->languagePolicy->defaultSystemInstruction();
        }

        return $this->languagePolicy->withOutputLanguageRule($templateInstruction);
    }

    private function buildUserPrompt(string $inputContent, ?string $targetPlatform = null): string
    {
        $prompt = "Phân tích nội dung sau thành một prompt video:\n\n" . $inputContent;

        if (!empty($targetPlatform)) {
            $platformInstructions = match (strtolower($targetPlatform)) {
                'runway' => 'Nền tảng đích: Runway Gen-3. Tập trung vào chuyển động điện ảnh, camera tracking, ánh sáng động và chi tiết video chân thực.',
                'sora' => 'Nền tảng đích: OpenAI Sora. Tập trung vào vật lý cảnh quay phong phú, chiều sâu tường thuật, điều khiển chuyển động chính xác và tính nhất quán không gian cao.',
                'kling' => 'Nền tảng đích: Kling AI. Tập trung vào chuyển động chủ thể rõ ràng, chuyển động mượt mà chân thực, góc máy ấn tượng và kết cấu sống động.',
                'midjourney' => 'Nền tảng đích: Midjourney v6 / Animation. Tập trung vào phong cách nghệ thuật, chỉnh màu, thông số nhiếp ảnh và không khí sống động.',
                default => 'Nền tảng đích: ' . ucfirst($targetPlatform) . '. Tùy chỉnh prompt để tối ưu cho engine tạo video này.',
            };
            $prompt .= "\n\n" . $platformInstructions;
        }

        return $prompt;
    }

    private function getVideoPromptSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'subject' => [
                    'type' => 'string',
                    'description' => 'Chủ thể chính trong video',
                ],
                'action' => [
                    'type' => 'string',
                    'description' => 'Hành động của chủ thể',
                ],
                'environment' => [
                    'type' => 'string',
                    'description' => 'Bối cảnh và môi trường xung quanh',
                ],
                'camera_movement' => [
                    'type' => 'string',
                    'description' => 'Góc máy và chuyển động camera',
                ],
                'lighting_and_atmosphere' => [
                    'type' => 'string',
                    'description' => 'Ánh sáng và không khí',
                ],
                'final_video_prompt' => [
                    'type' => 'string',
                    'description' => 'Prompt video hoàn chỉnh bằng '
                        . $this->languagePolicy->languageName()
                        . ', sẵn sàng cho API tạo video',
                ],
            ],
            'required' => [
                'subject',
                'action',
                'environment',
                'camera_movement',
                'lighting_and_atmosphere',
                'final_video_prompt',
            ],
        ];
    }

    public function refine(ContentPrompt $contentPrompt, string $instruction, array $fields = ['all']): array
    {
        $template = $contentPrompt->template;

        $systemInstruction = $this->buildSystemInstruction($template?->system_instruction);

        $systemInstruction .= "\n\n" . $this->getRefinementGuardrail();

        $userPrompt = $this->buildRefinementPrompt(
            $contentPrompt->input_content,
            $contentPrompt->target_platform,
            $contentPrompt->analyzed_structure ?? [],
            $instruction,
            $fields
        );

        $data = $this->geminiClient->generateContent(
            $systemInstruction,
            $userPrompt,
            $this->getVideoPromptSchema()
        );

        if (!is_array($data) || empty($data['final_video_prompt'])) {
            throw new RuntimeException('Gemini trả về phản hồi không hợp lệ hoặc thiếu dữ liệu trong quá trình tinh chỉnh.');
        }

        return $data;
    }

    private function getRefinementGuardrail(): string
    {
        return 'Bạn đang tinh chỉnh một kết quả JSON đã tạo trước đó, không phải tạo mới. '
            . 'Giữ nguyên mọi trường trừ những trường được liệt kê cần chỉnh sửa. '
            . 'Với các trường được liệt kê, hãy làm chúng rõ ràng, cụ thể và chi tiết hơn dựa trên '
            . 'yêu cầu của người dùng — không thay đổi chủ thể hoặc ý nghĩa cốt lõi trừ khi yêu cầu chỉnh sửa đòi hỏi.';
    }

    private function buildRefinementPrompt(
        string $inputContent,
        ?string $targetPlatform,
        array $previousResult,
        string $instruction,
        array $fields
    ): string {
        $fieldsToRevise = in_array('all', $fields, true)
            ? 'tất cả các trường'
            : implode(', ', $fields);

        $prompt = "Nội dung nguồn gốc:\n{$inputContent}\n\n";
        $prompt .= "Kết quả có cấu trúc trước đó (JSON):\n" . json_encode($previousResult, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Các trường cần chỉnh sửa: {$fieldsToRevise}\n";
        $prompt .= "Yêu cầu chỉnh sửa của người dùng: \"{$instruction}\"\n\n";
        $prompt .= 'Hãy trả lại toàn bộ đối tượng JSON theo đúng schema. Chỉ chỉnh sửa các trường được yêu cầu '
            . 'cho rõ ràng và chi tiết hơn, sau đó cập nhật final_video_prompt để phản ánh các thay đổi '
            . 'đã thực hiện ở các trường khác.';

        if (!empty($targetPlatform)) {
            $prompt .= "\n\nNền tảng đích vẫn là: {$targetPlatform}.";
        }

        return $prompt;
    }
}
