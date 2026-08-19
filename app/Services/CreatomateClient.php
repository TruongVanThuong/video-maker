<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CreatomateClient
{
    protected string $apiKey;
    protected string $endpoint;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('video.video.provider') === 'creatomate'
            ? (string) config('video.creatomate.api_key')
            : (string) config('video.creatomate.api_key'); // default to creatomate API key mapping
            
        $this->endpoint = rtrim((string) config('video.creatomate.endpoint'), '/');
        $this->timeout = (int) config('video.creatomate.timeout', 30);
    }

    /**
     * Sends render request to Creatomate API.
     *
     * @param array $payload
     * @return array
     * @throws RuntimeException
     */
    public function sendRenderRequest(array $payload): array
    {
        Log::info('CreatomateClient: Sending render request.', [
            'payload_keys' => array_keys($payload),
        ]);

        if (empty($this->apiKey)) {
            throw new RuntimeException('Creatomate API Key is not configured.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->post($this->endpoint . '/renders', $payload);

        if ($response->failed()) {
            Log::error('CreatomateClient: Render request failed.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException("Creatomate render request failed: Status {$response->status()}, {$response->body()}");
        }

        return $response->json();
    }

    /**
     * Fetches current render status from Creatomate API.
     *
     * @param string $jobId
     * @return array
     * @throws RuntimeException
     */
    public function fetchRenderStatus(string $jobId): array
    {
        Log::info('CreatomateClient: Fetching render status.', ['job_id' => $jobId]);

        if (empty($this->apiKey)) {
            throw new RuntimeException('Creatomate API Key is not configured.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->get($this->endpoint . '/renders/' . $jobId);

        if ($response->failed()) {
            Log::error('CreatomateClient: Fetch status request failed.', [
                'job_id' => $jobId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException("Creatomate fetch status failed: Status {$response->status()}, {$response->body()}");
        }

        return $response->json();
    }
}
