<?php

namespace App\Services;

use App\Models\ContentPrompt;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PromptAnalyzerService
{
    public function analyze(ContentPrompt $contentPrompt): array
    {
        $template = $contentPrompt->template;

        $systemInstruction = $template?->system_instruction
            ?? 'You are an expert AI Video Prompt Engineer. Analyze the user content and generate structured parameters for AI Video Generators (Sora/Runway/Kling).';

        $jsonSchema = [
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
            'additionalProperties' => false,
        ];

        $prompt = <<<PROMPT
{$systemInstruction}

Analyze the following content into a video prompt.

User content:
{$contentPrompt->input_content}
PROMPT;

        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $apiKey = config('services.gemini.key');
        dd([
                'model' => $model,
                'has_api_key' => !empty($apiKey),
                'api_key_length' => strlen($apiKey ?? ''),
                'url' => "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
            ]);
        if (!$apiKey) {
            throw new RuntimeException(
                'Gemini API key is not configured.'
            );
        }

        $response = Http::timeout(60)
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                [
                    'system_instruction' => [
                        'parts' => [
                            [
                                'text' => $systemInstruction,
                            ],
                        ],
                    ],

                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                [
                                    'text' => "Analyze the following content into a video prompt:\n\n"
                                        . $contentPrompt->input_content,
                                ],
                            ],
                        ],
                    ],

                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $jsonSchema,
                    ],
                ]
            );
        dd([
            'status' => $response->status(),
            'successful' => $response->successful(),
            'failed' => $response->failed(),
            'body' => $response->body(),
        ]);
        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini API error: ' . $response->body()
            );
        }

        $content = $response->json(
            'candidates.0.content.parts.0.text'
        );

        if (!$content) {
            throw new RuntimeException(
                'Gemini returned an empty response.'
            );
        }

        $data = json_decode($content, true);

        if (!is_array($data)) {
            throw new RuntimeException(
                'Gemini returned invalid JSON: ' . $content
            );
        }

        if (empty($data['final_video_prompt'])) {
            throw new RuntimeException(
                'Gemini response is missing final_video_prompt.'
            );
        }

        return $data;
    }
}