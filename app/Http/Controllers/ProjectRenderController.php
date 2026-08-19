<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Project;
use App\Jobs\BuildVideoTimelineJob;
use Illuminate\Http\JsonResponse;

class ProjectRenderController extends Controller
{
    /**
     * Trigger the video rendering pipeline for the given project.
     *
     * @param Project $project
     * @return JsonResponse
     */
    public function render(Project $project): JsonResponse
    {
        // Simple validation: make sure project has scenes before starting
        if ($project->scenes()->count() === 0) {
            return response()->json([
                'message' => 'Project has no scenes to render.',
            ], 422);
        }

        // Check if already rendering
        if ($project->status === 'rendering') {
            return response()->json([
                'message' => 'Project is already rendering.',
                'render_job_id' => $project->render_job_id,
            ], 422);
        }

        // Dispatch background rendering job
        BuildVideoTimelineJob::dispatch($project->id);

        return response()->json([
            'message' => 'Rendering pipeline triggered successfully.',
            'project_id' => $project->id,
            'status' => 'processing',
        ], 202);
    }
}
