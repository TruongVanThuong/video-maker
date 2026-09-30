<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreatomateWebhookController extends Controller
{
    /**
     * Handle inbound webhook notifications from the Creatomate render engine.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->input();

        if (! $this->signatureIsValid($request)) {
            return response()->json(['message' => 'Invalid webhook signature.'], 403);
        }

        $renderJobId = (string) ($payload['id'] ?? '');

        if ($renderJobId === '') {
            return response()->json(['message' => 'Missing render job id.'], 422);
        }

        $project = Project::where('render_job_id', $renderJobId)->first();

        if ($project === null) {
            return response()->json(['message' => 'Unknown render job id.'], 404);
        }

        $status = strtolower((string) ($payload['status'] ?? ''));

        if ($status === 'finished' || $status === 'succeeded') {
            $project->update([
                'status' => Project::STATUS_COMPLETED,
                'video_path' => (string) ($payload['url'] ?? $project->video_path),
                'rendered_at' => now(),
                'render_error' => null,
            ]);
        } elseif (in_array($status, ['failed', 'error', 'canceled', 'cancelled'], true)) {
            $project->update([
                'status' => Project::STATUS_FAILED,
                'render_error' => (string) ($payload['error'] ?? $payload['message'] ?? 'Creatomate reported a failure.'),
            ]);
        } else {
            // Still rendering: keep current state. Respond OK so the provider stops retrying.
        }

        return response()->json(['message' => 'ok']);
    }

    private function signatureIsValid(Request $request): bool
    {
        $token = config('services.creatomate.webhook_token');

        // Khi không cấu hình webhook token, render_job_id chính là lớp xác thực nhẹ.
        if (empty($token)) {
            return true;
        }

        $headerValue = trim((string) $request->header('X-Creatomate-Signature'));

        return hash_equals($token, $headerValue);
    }
}
