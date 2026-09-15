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

        $userPrompt = $this->buildUserPrompt($contentPrompt->input_content, $contentPrompt->target_platform);
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

    private function buildUserPrompt(string $inputContent, ?string $targetPlatform = null): string
    {
        $prompt = "Analyze the following content into a video prompt:\n\n" . $inputContent;

        if (!empty($targetPlatform)) {
            $platformInstructions = match (strtolower($targetPlatform)) {
                'runway' => 'Target Platform: Runway Gen-3. Focus on cinematic movement, camera tracking, dynamic lighting, and photorealistic video detail.',
                'sora' => 'Target Platform: OpenAI Sora. Focus on rich scene physics, narrative depth, precise motion control, and high spatial consistency.',
                'kling' => 'Target Platform: Kling AI. Focus on clear subject motions, realistic fluid movements, dramatic camera angles, and vivid textures.',
                'midjourney' => 'Target Platform: Midjourney v6 / Animation. Focus on artistic styling, color grading, photographic parameters, and vivid atmospheric cues.',
                default => "Target Platform: " . ucfirst($targetPlatform) . ". Tailor prompt specifically for optimal video generation on this engine.",
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

    public function refine(ContentPrompt $contentPrompt, string $instruction, array $fields = ['all']): array
    {
        $template = $contentPrompt->template;

        $systemInstruction = $template?->system_instruction
            ?? $this->getDefaultSystemInstruction();

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
            throw new RuntimeException('Gemini returned invalid or incomplete response during refinement.');
        }

        return $data;
    }

    private function getRefinementGuardrail(): string
    {
        return 'You are refining a previously generated JSON result, not creating a new one. '
            . 'Keep every field exactly as-is unless it is explicitly listed as a field to revise. '
            . 'For the listed fields, make them clearer, more specific, and more detailed based on the '
            . "user's instruction — do not change the core subject or meaning unless the instruction asks for it.";
    }

    private function buildRefinementPrompt(
        string $inputContent,
        ?string $targetPlatform,
        array $previousResult,
        string $instruction,
        array $fields
    ): string {
        $fieldsToRevise = in_array('all', $fields, true)
            ? 'all fields'
            : implode(', ', $fields);

        $prompt = "Original source content:\n{$inputContent}\n\n";
        $prompt .= "Previous structured result (JSON):\n" . json_encode($previousResult, JSON_PRETTY_PRINT) . "\n\n";
        $prompt .= "Fields to revise: {$fieldsToRevise}\n";
        $prompt .= "User's refinement instruction: \"{$instruction}\"\n\n";
        $prompt .= 'Return the complete JSON object again in the same schema. Revise only the requested '
            . 'fields to be clearer and more detailed, then update final_video_prompt to reflect any changes '
            . 'made to the other fields.';

        if (!empty($targetPlatform)) {
            $prompt .= "\n\nTarget platform remains: {$targetPlatform}.";
        }

        return $prompt;
    }
}
