<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Project;
use App\Services\CreatomateRenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Throwable;

class PollRenderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 60;

    public int $timeout = 60;

    public int $backoff = 15;

    public function __construct(
        public Project $project
    ) {}

    public function handle(CreatomateRenderService $renderService): void
    {
        if (in_array($this->project->status, [Project::STATUS_COMPLETED, Project::STATUS_FAILED], true)) {
            return;
        }

        if (empty($this->project->render_job_id)) {
            throw new RuntimeException('Project has no render_job_id to poll.');
        }

        try {
            $status = $renderService->getRenderStatus($this->project->render_job_id);
        } catch (Throwable $e) {
            if ($this->attempts() >= $this->tries) {
                throw $e;
            }

            $this->release($this->backoff);

            return;
        }

        if ($status['status'] === 'finished' || $status['status'] === 'succeeded') {
            $this->project->update([
                'status' => Project::STATUS_COMPLETED,
                'video_path' => $status['url'],
                'rendered_at' => now(),
                'render_error' => null,
            ]);

            return;
        }

        if (in_array($status['status'], ['failed', 'error', 'canceled', 'cancelled'], true)) {
            $this->project->update([
                'status' => Project::STATUS_FAILED,
                'render_error' => $status['error'] ?: 'External render engine reported a failure.',
            ]);

            return;
        }

        $this->release($this->backoff);
    }

    public function failed(Throwable $e): void
    {
        $this->project->update([
            'status' => Project::STATUS_FAILED,
            'render_error' => 'Render timeout: '.$e->getMessage(),
        ]);
    }
}
