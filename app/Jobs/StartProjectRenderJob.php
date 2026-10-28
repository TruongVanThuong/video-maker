<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Project;
use App\Services\CreatomateRenderService;
use App\Services\SceneAssetGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class StartProjectRenderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        public Project $project
    ) {}

    public function handle(
        SceneAssetGenerator $assetGenerator,
        CreatomateRenderService $renderService
    ): void {
        foreach ($this->project->scenes()->get() as $scene) {
            $assetGenerator->ensureVoice($scene);
            $assetGenerator->ensureImage($scene);
        }

        $renderJobId = $renderService->renderProjectVideo($this->project);

        $this->project->update([
            'status' => Project::STATUS_RENDERING,
            'render_job_id' => $renderJobId,
            'render_error' => null,
        ]);

        // Khi không cấu hình webhook (chế độ mock/dev), dùng polling để hoàn tất.
        if (! $renderService->expectsWebhooks()) {
            PollRenderStatusJob::dispatch($this->project)
                ->delay(now()->addSeconds(5));
        }
    }

    public function failed(Throwable $e): void
    {
        $this->project->update([
            'status' => Project::STATUS_FAILED,
            'render_error' => $e->getMessage(),
        ]);

        report($e);
    }
}
