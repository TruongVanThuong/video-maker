<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class CreatomateRenderService
{
    private ?string $apiKey;
    private ?string $templateId;

    public function __construct()
    {
        $this->apiKey = config('services.creatomate.key', env('CREATOMATE_API_KEY'));
        $this->templateId = config('services.creatomate.template_id', env('CREATOMATE_TEMPLATE_ID'));
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
                'source' => $scene->image_path,
                'duration' => $scene->duration ?: 4.0,
            ];
            if ($scene->audio_path) {
                $elements[] = [
                    'type' => 'audio',
                    'source' => $scene->audio_path,
                    'duration' => $scene->duration ?: 4.0,
                ];
            }
        }

        if (!empty($this->apiKey) && !empty($this->templateId)) {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.creatomate.com/v1/renders', [
                'template_id' => $this->templateId,
                'modifications' => [
                    'Title' => $project->title,
                ],
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Creatomate Render API Error: ' . $response->body());
            }

            return (string) $response->json('0.id', Str::uuid()->toString());
        }

        // Mock Render Job ID for development/testing
        return 'render_job_' . Str::random(12);
    }
}
