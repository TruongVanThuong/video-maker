<?php

namespace App\Services;

use App\Models\ContentPrompt;
use RuntimeException;

class PromptAnalyzerService
{
    private GeminiApiClient $geminiClient;

    public function __construct(GeminiApiClient $geminiClient)
    {
        $this->geminiClient = $geminiClient;
    }

    public function analyze(ContentPrompt $contentPrompt): array
    {
        $template = $contentPrompt->template;

        $systemInstruction = $template?->system_instruction
            ?? $this->getDefaultSystemInstruction();

        $userPrompt = $this->buildUserPrompt($contentPrompt->input_content);
        $jsonSchema = $this->getVideoPromptSchema();

        $data = $this->geminiClient->generateContent(
            $systemInstruction,
            $userPrompt,
            $jsonSchema
        );

        if (!is_array($data) || empty($data['final_video_prompt'])) {
            throw new RuntimeException('Gemini returned invalid or incomplete response.');
        }

        return $data;
    }

    private function getDefaultSystemInstruction(): string
    {
        return 'You are an expert AI Video Prompt Engineer. '
            . 'Analyze the user content and generate structured parameters '
            . 'for AI Video Generators (Sora/Runway/Kling).';
    }

    private function buildUserPrompt(string $inputContent): string
    {
        return "Analyze the following content into a video prompt:\n\n" . $inputContent;
    }

    private function getVideoPromptSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'subject' => [
                    'type' => 'string',
                    'description' => 'Main subject in the video',
                ],
                'action' => [
                    'type' => 'string',
                    'description' => 'Action performed by the subject',
                ],
                'environment' => [
                    'type' => 'string',
                    'description' => 'Environment and surrounding context',
                ],
                'camera_movement' => [
                    'type' => 'string',
                    'description' => 'Camera angle and movement',
                ],
                'lighting_and_atmosphere' => [
                    'type' => 'string',
                    'description' => 'Lighting and atmosphere',
                ],
                'final_video_prompt' => [
                    'type' => 'string',
                    'description' => 'Complete English video prompt ready for a video generation API',
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
}
