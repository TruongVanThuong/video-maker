<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class VideoRenderService
{
    protected CreatomateClient $client;

    public function __construct(CreatomateClient $client)
    {
        $this->client = $client;
    }

    /**
     * Builds the Creatomate RenderScript representation from a project and its scenes.
     *
     * @param Project $project
     * @return array
     */
    public function buildTimeline(Project $project): array
    {
        Log::info('VideoRenderService: Building timeline.', ['project_id' => $project->id]);

        $accumulatedTime = 0.0;
        $elements = [];

        foreach ($project->scenes as $scene) {
            $duration = $scene->duration > 0 ? (float) $scene->duration : 5.0;

            // 1. Add Image element if present
            if (!empty($scene->image_path)) {
                $imageUrl = $this->getPublicUrl($scene->image_path);
                
                $imageElement = [
                    'type' => 'image',
                    'source' => $imageUrl,
                    'time' => $accumulatedTime,
                    'duration' => $duration,
                    'track' => 1,
                    'clip' => true,
                ];

                // Map camera rule to animations
                $animations = $this->mapCameraRuleToAnimations($scene->camera_rule, $duration);
                if (!empty($animations)) {
                    $imageElement['animations'] = $animations;
                }

                $elements[] = $imageElement;
            }

            // 2. Add Audio element if present
            if (!empty($scene->audio_path)) {
                $audioUrl = $this->getPublicUrl($scene->audio_path);

                $elements[] = [
                    'type' => 'audio',
                    'source' => $audioUrl,
                    'time' => $accumulatedTime,
                    'duration' => $duration,
                    'track' => 2,
                ];
            }

            // 3. Subtitles Overlay (Text element) if dialogue is present
            if (!empty($scene->dialogue)) {
                $elements[] = [
                    'type' => 'text',
                    'text' => $scene->dialogue,
                    'time' => $accumulatedTime,
                    'duration' => $duration,
                    'track' => 3,
                    'x' => '50%',
                    'y' => '85%',
                    'width' => '80%',
                    'align' => 'center',
                    'font_family' => 'Outfit', // Premium modern typography
                    'font_size' => '28px',
                    'fill_color' => '#ffffff',
                    'stroke_color' => '#000000',
                    'stroke_width' => '6px',
                    'shadow_color' => 'rgba(0, 0, 0, 0.5)',
                    'shadow_blur' => '8px',
                ];
            }

            $accumulatedTime += $duration;
        }

        return [
            'output_format' => 'mp4',
            'width' => 1920,
            'height' => 1080,
            'elements' => $elements,
        ];
    }

    /**
     * Maps database camera rules to Creatomate RenderScript animations.
     *
     * @param mixed $cameraRule
     * @param float $duration
     * @return array
     */
    protected function mapCameraRuleToAnimations(mixed $cameraRule, float $duration): array
    {
        if (empty($cameraRule)) {
            return [];
        }

        $rule = is_array($cameraRule) ? $cameraRule : json_decode((string) $cameraRule, true);
        if (!$rule) {
            return [];
        }

        $type = $rule['type'] ?? '';
        $speed = $rule['speed'] ?? 'medium';
        $animations = [];

        if ($type === 'zoom_in') {
            $animations[] = [
                'type' => 'scale',
                'scope' => 'element',
                'start_scale' => '100%',
                'end_scale' => $speed === 'fast' ? '125%' : ($speed === 'slow' ? '108%' : '115%'),
                'easing' => 'linear',
                'fade' => false,
            ];
        } elseif ($type === 'zoom_out') {
            $animations[] = [
                'type' => 'scale',
                'scope' => 'element',
                'start_scale' => $speed === 'fast' ? '125%' : ($speed === 'slow' ? '108%' : '115%'),
                'end_scale' => '100%',
                'easing' => 'linear',
                'fade' => false,
            ];
        } elseif ($type === 'pan_left') {
            $animations[] = [
                'type' => 'pan',
                'x' => '-15%',
                'y' => '0%',
                'easing' => 'linear',
            ];
        } elseif ($type === 'pan_right') {
            $animations[] = [
                'type' => 'pan',
                'x' => '15%',
                'y' => '0%',
                'easing' => 'linear',
            ];
        } elseif ($type === 'shake_and_zoom' || $type === 'shake') {
            $animations[] = [
                'type' => 'scale',
                'scope' => 'element',
                'start_scale' => '100%',
                'end_scale' => '115%',
                'easing' => 'linear',
            ];
        }

        return $animations;
    }

    /**
     * Resolves a media file path (relative or absolute) to a public URL.
     *
     * @param string $path
     * @return string
     */
    protected function getPublicUrl(string $path): string
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        return Storage::disk(config('filesystems.default'))->url($path);
    }

    /**
     * Triggers the render on the provider client and returns the response metadata.
     *
     * @param Project $project
     * @return array
     */
    public function render(Project $project): array
    {
        $timeline = $this->buildTimeline($project);

        $webhookUrl = config('video.webhook_url');
        $webhookToken = config('video.webhook_token');

        if (!empty($webhookUrl)) {
            if (!empty($webhookToken)) {
                $webhookUrl .= (parse_url($webhookUrl, PHP_URL_QUERY) ? '&' : '?') . 'token=' . urlencode($webhookToken);
            }
            $timeline['webhook_url'] = $webhookUrl;
        }

        $timeline['metadata'] = json_encode([
            'project_id' => $project->id,
        ]);

        return $this->client->sendRenderRequest($timeline);
    }

    /**
     * Retrives the render job status from Creatomate.
     *
     * @param string $renderJobId
     * @return array
     */
    public function checkStatus(string $renderJobId): array
    {
        return $this->client->fetchRenderStatus($renderJobId);
    }
}
