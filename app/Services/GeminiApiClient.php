<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiApiClient
{
    private string $apiKey;
    private string $model;
    private string $baseUrl;
    private int $timeout;
    private int $retryTimes;
    private int $retryDelayMs;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->model = config('services.gemini.model', 'gemini-2.5-flash');
        $this->baseUrl = config('services.gemini.base_url');
        $this->timeout = config('services.gemini.timeout', 60);
        $this->retryTimes = config('services.gemini.retry_times', 3);
        $this->retryDelayMs = config('services.gemini.retry_delay_ms', 2000);

        if (!$this->apiKey) {
            throw new RuntimeException('Gemini API key is not configured.');
        }
    }

    public function generateContent(
        string $systemInstruction,
        string $userPrompt,
        ?array $responseSchema = null
    ): array {
        $payload = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $userPrompt],
                    ],
                ],
            ],
        ];

        if ($responseSchema) {
            $payload['generationConfig'] = [
                'responseMimeType' => 'application/json',
                'responseSchema' => $responseSchema,
            ];
        }

        $response = $this->buildHttpClient()
            ->post(
                "{$this->baseUrl}/models/{$this->model}:generateContent",
                $payload
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Gemini API error: ' . $response->body()
            );
        }

        $content = $response->json('candidates.0.content.parts.0.text');

        if (!$content) {
            throw new RuntimeException('Gemini returned an empty response.');
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Gemini returned invalid JSON.');
        }

        return $data;
    }

    private function buildHttpClient(): PendingRequest
    {
        return Http::timeout($this->timeout)
            ->retry($this->retryTimes, $this->retryDelayMs, function ($exception) {
                return $exception instanceof \Illuminate\Http\Client\ConnectionException
                    || ($exception->response && $exception->response->status() === 503);
            })
            ->withHeaders([
                'X-goog-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ]);
    }
}
