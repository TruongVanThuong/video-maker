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
        ];

        // Dùng model tương thích với Gemini API
        $model = config('services.gemini.model', 'gemini-flash-latest');
        $apiKey = config('services.gemini.key');

        if (!$apiKey) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        // Tự động retry 3 lần, mỗi lần cách nhau 2000ms (2 giây) nếu dính lỗi 503 hoặc timeout
        $response = Http::timeout(60)
            ->retry(3, 2000, function ($exception, $request) {
                // Chỉ retry khi dính lỗi kết nối hoặc mã trả về là 503 (High Demand)
                return $exception instanceof \Illuminate\Http\Client\ConnectionException || 
                    ($exception->response && $exception->response->status() === 503);
            })
            ->withHeaders([
                'X-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'system_instruction' => [
                    'parts' => [
                        ['text' => $systemInstruction],
                    ],
                ],
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => "Analyze the following content into a video prompt:\n\n" . $contentPrompt->input_content],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                    'responseSchema' => $jsonSchema,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini API error: ' . $response->body());
        }

        $content = $response->json('candidates.0.content.parts.0.text');

        if (!$content) {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        $data = json_decode($content, true);

        if (!is_array($data) || empty($data['final_video_prompt'])) {
            throw new RuntimeException('Gemini returned invalid or incomplete JSON.');
        }

        return $data;
    }
}