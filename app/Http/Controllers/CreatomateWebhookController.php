<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class CreatomateWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $expectedToken = (string) config('services.creatomate.webhook_token');

        if (empty($expectedToken) || !hash_equals($expectedToken, (string) $request->query('token'))) {
            abort(401, 'Token webhook không hợp lệ.');
        }

        $renderJobId = (string) $request->input('id');
        $status = strtolower((string) $request->input('status'));
        $videoUrl = $request->input('url');
        $errorMessage = $request->input('error_message');

        $project = Project::where('render_job_id', $renderJobId)->first();

        if (!$project) {
            return response()->json(['message' => 'Unknown render job'], 404);
        }

        if ($status === 'succeeded') {
            $project->update([
                'status' => Project::STATUS_COMPLETED,
                'video_path' => $videoUrl,
                'rendered_at' => now(),
            ]);
        } elseif ($status === 'failed') {
            $project->update([
                'status' => 'failed',
                'render_error' => $errorMessage ?: 'Render thất bại từ phía Creatomate.',
            ]);
        }
        // Các status trung gian (planned/rendering) không cần xử lý, bỏ qua.

        return response()->json(['ok' => true]);
    }
}