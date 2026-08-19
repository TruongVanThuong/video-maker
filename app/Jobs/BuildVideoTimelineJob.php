<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Project;
use App\Models\AiJob;
use App\Services\VideoRenderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class BuildVideoTimelineJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public array $backoff = [10, 30, 60];

    protected int $projectId;

    /**
     * Create a new job instance.
     *
     * @param int $projectId
     */
    public function __construct(int $projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * Execute the job.
     *
     * @param VideoRenderService $videoRenderService
     * @return void
     */
    public function handle(VideoRenderService $videoRenderService): void
    {
        Log::info("BuildVideoTimelineJob: Starting rendering pipeline for project ID: {$this->projectId}");

        // Load project and eager load scenes
        $project = Project::with('scenes')->findOrFail($this->projectId);

        // Update status to processing
        $project->update(['status' => 'processing']);

        // Create tracking AiJob
        $aiJob = AiJob::create([
            'project_id' => $project->id,
            'job_type' => 'render_video',
            'provider' => config('video.provider', 'creatomate'),
            'status' => 'processing',
        ]);

        try {
            // Send render request through VideoRenderService
            $response = $videoRenderService->render($project);

            $renderJobId = $response['id'] ?? null;
            if (!$renderJobId) {
                throw new \RuntimeException('Creatomate render request did not return a valid render job ID.');
            }

            // Save render_job_id and update status to rendering
            $project->update([
                'render_job_id' => $renderJobId,
                'status' => 'rendering',
            ]);

            // Update AiJob details
            $aiJob->update([
                'response_data' => $response,
            ]);

            Log::info("BuildVideoTimelineJob: Project ID {$this->projectId} successfully queued for rendering. Job ID: {$renderJobId}");

        } catch (Throwable $exception) {
            Log::error("BuildVideoTimelineJob: Error executing project ID {$this->projectId}: " . $exception->getMessage(), [
                'exception' => $exception,
            ]);

            $aiJob->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            // Release back to queue if under max attempts
            if ($this->attempts() < $this->tries) {
                throw $exception;
            }

            // Otherwise, mark project as failed
            $project->update(['status' => 'failed']);
            throw $exception;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error("BuildVideoTimelineJob: Job failed permanently for project ID: {$this->projectId}. Message: " . $exception->getMessage());

        $project = Project::find($this->projectId);
        if ($project) {
            $project->update(['status' => 'failed']);
        }
    }
}
