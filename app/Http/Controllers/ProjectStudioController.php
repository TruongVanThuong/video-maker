<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSceneRequest;
use App\Jobs\StartProjectRenderJob;
use App\Models\Project;
use App\Models\Scene;
use App\Services\SceneAssetGenerator;
use Inertia\Inertia;
use Throwable;

class ProjectStudioController extends Controller
{
    public function __construct(
        private readonly SceneAssetGenerator $assetGenerator
    ) {}

    public function studio(Project $project)
    {
        $this->authorizeOwner($project);

        $project->load(['scenes' => function ($query) {
            $query->orderBy('scene_index', 'asc');
        }, 'contentPrompt']);

        $voiceProfiles = [
            ['id' => 'Kore', 'name' => 'Kore (Nam, Trầm & Ổn định)'],
            ['id' => 'Puck', 'name' => 'Puck (Nam, Đàm thoại)'],
            ['id' => 'Charon', 'name' => 'Charon (Nam, Thấp & Điềm tĩnh)'],
            ['id' => 'Fenrir', 'name' => 'Fenrir (Nam, Sâu & Trầm ấm)'],
            ['id' => 'Leda', 'name' => 'Leda (Nữ, Rõ ràng & Tươi sáng)'],
            ['id' => 'Orus', 'name' => 'Orus (Nữ, Mềm mại & Ấm áp)'],
            ['id' => 'Zephyr', 'name' => 'Zephyr (Nam, Nhẹ nhàng)'],
            ['id' => 'Aoede', 'name' => 'Aoede (Nữ, Biểu cảm)'],
        ];

        return Inertia::render('Project/Studio', [
            'project' => $project,
            'voiceProfiles' => $voiceProfiles,
        ]);
    }

    public function updateScene(UpdateSceneRequest $request, Scene $scene)
    {
        $validated = $request->validated();
        $scene->update($validated);

        return response()->json([
            'message' => 'Cập nhật phân cảnh thành công.',
            'scene' => $scene->fresh(),
        ]);
    }

    public function generateVoice(Scene $scene)
    {
        $this->authorizeOwner($scene->project);

        try {
            $this->assetGenerator->ensureVoice($scene);

            return response()->json([
                'success' => true,
                'message' => 'Đã sinh Voice thử thành công.',
                'scene' => $scene->fresh(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi sinh Voice thử: '.$e->getMessage(),
            ], 422);
        }
    }

    public function generateImage(Scene $scene)
    {
        $this->authorizeOwner($scene->project);

        try {
            $this->assetGenerator->ensureImage($scene);

            return response()->json([
                'success' => true,
                'message' => 'Đã sinh Ảnh thử thành công.',
                'scene' => $scene->fresh(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi sinh Ảnh thử: '.$e->getMessage(),
            ], 422);
        }
    }

    public function startRender(Project $project)
    {
        $this->authorizeOwner($project);

        if (! $project->scenes()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Dự án chưa có phân cảnh nào để render.',
            ], 422);
        }

        // Idempotency guard: chỉ chuyển trạng thái khi Project đang không được xử lý/render (hoặc force = true).
        $force = (bool) request()->boolean('force');
        $query = $project->newQuery()->whereKey($project->id);

        if (! $force) {
            $query->whereNotIn('status', [Project::STATUS_PROCESSING, Project::STATUS_RENDERING]);
        }

        $reserved = $query->update([
            'status' => Project::STATUS_PROCESSING,
            'render_job_id' => null,
            'video_path' => null,
            'rendered_at' => null,
            'render_error' => null,
        ]);

        if ($reserved === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Dự án đang được xử lý/render. Vui lòng chờ giây lát.',
                'project' => $project->fresh(['scenes']),
            ], 409);
        }

        StartProjectRenderJob::dispatch($project);

        return response()->json([
            'success' => true,
            'message' => 'Đã khởi chạy Batch Render Video Hoàn Chỉnh!',
            'project' => $project->fresh(['scenes']),
        ]);
    }

    public function renderStatus(Project $project)
    {
        $this->authorizeOwner($project);

        return response()->json([
            'project' => [
                'id' => $project->id,
                'status' => $project->status,
                'render_job_id' => $project->render_job_id,
                'video_url' => $project->video_url,
                'video_path' => $project->video_path,
                'render_error' => $project->render_error,
                'rendered_at' => $project->rendered_at,
            ],
        ]);
    }

    private function authorizeOwner(Project $project): void
    {
        abort_if($project->user_id !== auth()->id(), 403);
    }
}
