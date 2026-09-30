<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CreatomateRenderService
{
    private string $apiKey;

    private string $templateId;

    private string $endpoint;

    private int $timeout;

    public function __construct()
    {
        $this->apiKey = (string) config('services.creatomate.key');
        $this->templateId = (string) config('services.creatomate.template_id');
        $this->endpoint = rtrim((string) config('services.creatomate.endpoint', 'https://api.creatomate.com/v1'), '/');
        $this->timeout = (int) config('services.creatomate.timeout', 30);
    }

    /**
     * Start rendering full video project.
     *
     * @return string Render Job ID
     */
    public function renderProjectVideo(Project $project): string
    {
        $scenes = $project->scenes()->orderBy('scene_index', 'asc')->get();

        if ($scenes->isEmpty()) {
            throw new RuntimeException('Cannot render a project without scenes.');
        }

        $elements = [];
        foreach ($scenes as $scene) {
            $elements[] = [
                'type' => 'image',
                'source' => $this->toPublicUrl($scene->image_path),
                'duration' => $scene->duration ?: 4.0,
            ];
            if ($scene->audio_path) {
                $elements[] = [
                    'type' => 'audio',
                    'source' => $this->toPublicUrl($scene->audio_path),
                    'duration' => $scene->duration ?: 4.0,
                ];
            }
        }

        if (!empty($this->apiKey)) {
            $payload = [
                'template_id' => $this->templateId,
                'modifications' => [
                    'Title' => $project->title,
                ],
            ];

            if ($this->expectsWebhooks()) {
                $payload['webhook_url'] = $this->webhookUrl();
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->apiKey,
                'Content-Type' => 'application/json',
            ])
                ->timeout($this->timeout)
                ->post($this->endpoint.'/renders', $payload);

            if ($response->failed()) {
                throw new RuntimeException('Creatomate Render API Error: '.$response->body());
            }

            $jobId = $response->json('id') ?? $response->json('0.id');

            if (empty($jobId)) {
                throw new RuntimeException('Creatomate did not return a valid render ID: '.$response->body());
            }

            return (string) $jobId;
        }

        // Mock Render Job ID for development/testing
        return 'render_job_'.Str::random(12);
    }

    /**
     * Poll the external render engine for the current render status.
     *
     * @return array{status: string, url: ?string, error: ?string}
     */
    public function getRenderStatus(string $renderJobId): array
    {
        // Mock mode: simulate a successful render so the pipeline can complete.
        if (empty($this->apiKey)) {
            return [
                'status' => 'finished',
                'url' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                'error' => null,
            ];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout($this->timeout)
            ->get($this->endpoint.'/renders/'.$renderJobId);

        if ($response->failed()) {
            throw new RuntimeException('Creatomate Render Status API Error: '.$response->body());
        }

        $payload = $response->json();

        $status = strtolower((string) ($payload[0]['status'] ?? $payload['status'] ?? 'rendering'));
        $url = $payload[0]['url'] ?? $payload['url'] ?? null;
        $error = $payload[0]['error'] ?? $payload['error'] ?? null;

        return [
            'status' => $status === 'succeeded' ? 'finished' : $status,
            'url' => $url,
            'error' => $error,
        ];
    }

    /**
     * Whether real render jobs (and webhooks) are expected.
     */
    public function expectsWebhooks(): bool
    {
        if (empty($this->apiKey) || empty($this->templateId)) {
            return false;
        }

        $webhookUrl = $this->webhookUrl();
        $host = parse_url($webhookUrl, PHP_URL_HOST);

        if (empty($host) || in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true) || str_ends_with($host, '.test') || str_ends_with($host, '.local')) {
            return false;
        }

        return true;
    }

    private function toPublicUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function webhookUrl(): string
    {
        $url = (string) config('services.creatomate.webhook_url');

        if (preg_match('/^https?:\/\//i', $url) === 1) {
            return $url;
        }

        return url($url);
    }
}
