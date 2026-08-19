<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMedia;
use App\Models\AiJob;
use App\Events\VideoReady;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VideoWebhookController extends Controller
{
    /**
     * Handle incoming webhook requests from Creatomate.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        Log::info('VideoWebhookController: Received webhook request.', [
            'payload' => $request->all(),
            'query' => $request->query(),
        ]);

        // 1. Verify token signature if configured
        $configuredToken = config('video.webhook_token');
        if (!empty($configuredToken)) {
            $token = $request->query('token') ?? $request->header('X-Webhook-Token');
            if ($token !== $configuredToken) {
                Log::warning('VideoWebhookController: Invalid webhook token.');
                return response()->json(['message' => 'Unauthorized token.'], 401);
            }
        }

        // 2. Extract fields from payload
        $renderJobId = $request->input('id');
        $status = $request->input('status');
        $url = $request->input('url');
        $errorMessage = $request->input('error_message');

        if (empty($renderJobId)) {
            Log::warning('VideoWebhookController: Missing render job ID.');
            return response()->json(['message' => 'Missing render ID.'], 400);
        }

        // 3. Find the Project associated with the render job ID
        $project = Project::where('render_job_id', $renderJobId)->first();
        if (!$project) {
            Log::warning("VideoWebhookController: No project found matching render job ID: {$renderJobId}");
            return response()->json(['message' => 'Project not found.'], 404);
        }

        // 4. Implement idempotency: check if the project is already finalized
        if (in_array($project->status, ['completed', 'failed'], true)) {
            Log::info("VideoWebhookController: Project ID {$project->id} is already in a terminal state ({$project->status}). Skipping duplicate update.");
            return response()->json(['message' => 'Already processed.'], 200);
        }

        // 5. Handle terminal states
        if ($status === 'succeeded') {
            Log::info("VideoWebhookController: Render succeeded for Project ID: {$project->id}");

            // Calculate total duration from scenes
            $totalDuration = (float) $project->scenes()->sum('duration');

            // Fetch file size via HEAD request
            $fileSize = 0;
            if (!empty($url)) {
                try {
                    $headResponse = Http::head($url);
                    if ($headResponse->successful()) {
                        $fileSize = (int) $headResponse->header('Content-Length', '0');
                    }
                } catch (\Throwable $e) {
                    Log::warning("VideoWebhookController: Failed to fetch file size for project ID {$project->id}: " . $e->getMessage());
                }
            }

            // Download the final MP4 video and store it in our media storage
            $finalUrl = $url;
            $finalPath = '';
            if (!empty($url)) {
                try {
                    $downloadResponse = Http::get($url);
                    if ($downloadResponse->successful()) {
                        $disk = config('filesystems.default');
                        if ($disk === 'local') {
                            $disk = 'public';
                        }
                        
                        $filename = "renders/project_{$project->id}_" . time() . ".mp4";
                        Storage::disk($disk)->put($filename, $downloadResponse->body());
                        
                        $finalUrl = Storage::disk($disk)->url($filename);
                        $finalPath = $filename;
                        
                        Log::info("VideoWebhookController: Downloaded and stored final video file to disk: {$disk}, path: {$filename}");
                    } else {
                        Log::warning("VideoWebhookController: Failed to download MP4 from Creatomate URL: {$url}");
                    }
                } catch (\Throwable $e) {
                    Log::error("VideoWebhookController: Error downloading MP4 file for project {$project->id}: " . $e->getMessage());
                }
            }

            // Save ProjectMedia
            ProjectMedia::updateOrCreate(
                ['project_id' => $project->id],
                [
                    'media_type' => 'final_video',
                    'file_url' => $finalUrl,
                    'file_path' => $finalPath ?: (parse_url($url, PHP_URL_PATH) ?? ''),
                    'file_size' => $fileSize,
                    'total_duration' => $totalDuration,
                ]
            );

            // Update associated AiJob status to success
            $aiJob = AiJob::where('project_id', $project->id)
                ->where('job_type', 'render_video')
                ->latest()
                ->first();

            if ($aiJob) {
                $aiJob->update([
                    'status' => 'success',
                    'response_data' => $request->all(),
                ]);
            }

            // Update Project Status
            $project->update(['status' => 'completed']);

            // Dispatch VideoReady event
            event(new VideoReady($project));

            Log::info("VideoWebhookController: Updated Project ID {$project->id} to completed and dispatched VideoReady event.");
        } elseif ($status === 'failed') {
            Log::error("VideoWebhookController: Render failed for Project ID: {$project->id}. Error: {$errorMessage}");

            // Update associated AiJob status to failed
            $aiJob = AiJob::where('project_id', $project->id)
                ->where('job_type', 'render_video')
                ->latest()
                ->first();

            if ($aiJob) {
                $aiJob->update([
                    'status' => 'failed',
                    'response_data' => $request->all(),
                    'error_message' => $errorMessage,
                ]);
            }

            // Update Project Status
            $project->update(['status' => 'failed']);
        } else {
            Log::info("VideoWebhookController: Ignored status transition: {$status} for project ID: {$project->id}");
        }

        return response()->json(['message' => 'Processed successfully.'], 200);
    }
}
