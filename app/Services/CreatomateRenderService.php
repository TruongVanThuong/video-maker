<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\Scene;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class CreatomateRenderService
{
    private string $apiKey;
    private string $endpoint;
    private int $timeout;
    private int $outputWidth;
    private int $outputHeight;
    private int $frameRate;

    public function __construct()
    {
        $this->apiKey = (string) config('services.creatomate.key');
        $this->endpoint = rtrim((string) config('services.creatomate.endpoint', 'https://api.creatomate.com/v2'), '/');
        $this->timeout = (int) config('services.creatomate.timeout', 30);
        $this->outputWidth = (int) config('services.creatomate.output_width', 1920);
        $this->outputHeight = (int) config('services.creatomate.output_height', 1080);
        $this->frameRate = (int) config('services.creatomate.frame_rate', 30);
    }

    /**
     * Start rendering the full video project by sending a raw RenderScript
     * (no template_id — built dynamically from the project's scenes).
     *
     * @return string Render Job ID
     */
    public function renderProjectVideo(Project $project): string
    {
        $scenes = $project->scenes()->orderBy('scene_index', 'asc')->get();

        if ($scenes->isEmpty()) {
            throw new RuntimeException('Không thể render dự án không có cảnh nào.');
        }

        $this->assertScenesHaveRequiredAssets($scenes);

        $renderScript = $this->buildRenderScript($scenes);

        if (empty($this->apiKey)) {
            // Mock mode — không có API key thật, trả job id giả để luồng dev vẫn chạy được.
            return 'render_job_'.Str::random(12);
        }

        $response = $this->postRender($renderScript);

        $jobId = $response->json('id') ?? $response->json('0.id');

        if (empty($jobId)) {
            throw new RuntimeException('Creatomate did not return a valid render ID: '.$response->body());
        }

        return (string) $jobId;
    }

    /**
     * Validate a RenderScript against Creatomate without consuming render credits.
     * Dùng để test thủ công trước khi đẩy vào queue thật — trả về warnings nếu có
     * element nào bị parse sai hoặc asset không truy cập được.
     *
     * @return array{warnings: array<int, mixed>, valid: bool}
     */
    public function dryRun(Project $project): array
    {
        $scenes = $project->scenes()->orderBy('scene_index', 'asc')->get();

        if ($scenes->isEmpty()) {
            throw new RuntimeException('Không thể kiểm tra dự án không có cảnh nào.');
        }

        $renderScript = $this->buildRenderScript($scenes);

        $response = $this->postRender($renderScript, dryRun: true);

        if ($response->failed()) {
            throw new RuntimeException('Creatomate dry-run request failed: '.$response->body());
        }

        $warnings = $response->json('warnings') ?? $response->json('0.warnings') ?? [];

        return [
            'warnings' => $warnings,
            'valid' => empty($warnings),
        ];
    }

    /**
     * Poll the external render engine for the current render status.
     *
     * @return array{status: string, url: ?string, error: ?string}
     */
    public function getRenderStatus(string $renderJobId): array
    {
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
        $error = $payload[0]['error_message'] ?? $payload['error_message'] ?? null;

        return [
            'status' => $status === 'succeeded' ? 'finished' : $status,
            'url' => $url,
            'error' => $error,
        ];
    }

    public function expectsWebhooks(): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        $host = parse_url($this->webhookUrl(), PHP_URL_HOST);

        if (empty($host) || in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true)
            || str_ends_with($host, '.test') || str_ends_with($host, '.local')) {
            return false;
        }

        return true;
    }

    /**
     * Mỗi scene -> 1 composition chứa image + audio, các composition nối tiếp
     * nhau tự động trên cùng track (không khai báo "time") theo đúng thứ tự
     * scene_index. Children không khai báo "duration" sẽ tự fill theo
     * duration của composition cha — đây là hành vi mặc định của Creatomate.
     */
    private function buildRenderScript(Collection $scenes): array
    {
        $sceneCompositions = [];

        foreach ($scenes as $scene) {
            $sceneCompositions[] = $this->buildSceneComposition($scene);
        }

        return [
            'output_format' => 'mp4',
            'width' => $this->outputWidth,
            'height' => $this->outputHeight,
            'frame_rate' => $this->frameRate,
            'elements' => $sceneCompositions,
        ];
    }

    private function buildSceneComposition(Scene $scene): array
    {
        $duration = (float) ($scene->duration ?: 4.0);

        $children = [
            [
                'type' => 'image',
                'source' => $this->toPublicUrl($scene->image_path),
                'fit' => 'cover',
                'animations' => $this->buildCameraAnimation($scene->camera_rule, $duration),
            ],
        ];

        if ($scene->audio_path) {
            $children[] = [
                'type' => 'audio',
                'source' => $this->toPublicUrl($scene->audio_path),
            ];
        }

        return [
            'type' => 'composition',
            'track' => 1,
            'duration' => $duration,
            'elements' => $children,
        ];
    }

    /**
     * Ánh xạ camera_rule (đã lưu sẵn trong DB từ LLM) sang hiệu ứng Ken Burns
     * đơn giản của Creatomate. Đây là bonus tận dụng dữ liệu đã có — không
     * làm gì nếu camera_rule rỗng hoặc không khớp key mong đợi.
     */
    private function buildCameraAnimation(?array $cameraRule, float $duration): array
    {
        if (empty($cameraRule)) {
            return [];
        }

        $zoom = $cameraRule['zoom'] ?? null;

        if (!in_array($zoom, ['in', 'out'], true)) {
            return [];
        }

        return [[
            'time' => 0,
            'duration' => $duration,
            'easing' => 'linear',
            'type' => 'scale',
            'scale_x' => $zoom === 'in' ? '100% -> 115%' : '115% -> 100%',
            'scale_y' => $zoom === 'in' ? '100% -> 115%' : '115% -> 100%',
        ]];
    }

    private function postRender(array $renderScript, bool $dryRun = false)
    {
        $payload = $renderScript;

        if ($dryRun) {
            $payload['dry_run'] = true;
        }

        if (!$dryRun && $this->expectsWebhooks()) {
            $payload['webhook_url'] = $this->webhookUrl();
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout($this->timeout)
            ->post($this->endpoint.'/renders', $payload);

        if ($response->failed() && !$dryRun) {
            throw new RuntimeException('Creatomate Render API Error: '.$response->body());
        }

        return $response;
    }

    private function assertScenesHaveRequiredAssets(Collection $scenes): void
    {
        $missing = $scenes->filter(fn (Scene $scene) => empty($scene->image_path));

        if ($missing->isNotEmpty()) {
            $indexes = $missing->pluck('scene_index')->implode(', ');
            throw new RuntimeException("Scenes thiếu image_path, không thể render: scene_index [{$indexes}]");
        }
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
        $absolute = preg_match('/^https?:\/\//i', $url) === 1 ? $url : url($url);

        // Creatomate webhook KHÔNG hỗ trợ custom header, nên xác thực bằng
        // token gắn thẳng vào query string — webhook controller sẽ so khớp
        // token này khi nhận callback.
        $token = (string) config('services.creatomate.webhook_token');

        if (empty($token)) {
            return $absolute;
        }

        $separator = str_contains($absolute, '?') ? '&' : '?';

        return $absolute.$separator.'token='.urlencode($token);
    }
}