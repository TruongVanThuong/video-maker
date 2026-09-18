<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSceneRequest;
use App\Models\Project;
use App\Models\Scene;
use App\Services\CreatomateRenderService;
use App\Services\ElevenLabsService;
use App\Services\FalAiImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Inertia\Inertia;
use Throwable;

class ProjectStudioController extends Controller
{
    public function studio(Project $project)
    {
        $this->authorizeOwner($project);

        $project->load(['scenes' => function ($query) {
            $query->orderBy('scene_index', 'asc');
        }, 'contentPrompt']);

        $voiceProfiles = [
            ['id' => '21m00Tcm4TlvDq8ikWAM', 'name' => 'Rachel (Female, Calm & Clear)'],
            ['id' => 'AZnzlk1XvdvUeBnXmlld', 'name' => 'Domi (Female, Strong)'],
            ['id' => 'EXAVITQu4vr4xnSDxMaL', 'name' => 'Bella (Female, Soft & Expressive)'],
            ['id' => 'ErXwobaYiN019PkySvjV', 'name' => 'Antoni (Male, Deep & Smooth)'],
            ['id' => 'TxGEqnHWrfWFTfGW9XjX', 'name' => 'Josh (Male, Deep Narrative)'],
            ['id' => 'VR6AewLTigWG4xVOgGGU', 'name' => 'Arnold (Male, Crisp Storyteller)'],
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

    public function generateVoice(Scene $scene, ElevenLabsService $voiceService)
    {
        $this->authorizeOwner($scene->project);

        try {
            $voiceId = $scene->voice_id ?: '21m00Tcm4TlvDq8ikWAM';
            $result = $voiceService->generateVoice($scene->dialogue, $voiceId);

            $scene->update([
                'audio_path' => $result['audio_path'],
                'duration' => $result['duration'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã sinh Voice thử thành công.',
                'scene' => $scene->fresh(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi sinh Voice thử: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function generateImage(Scene $scene, FalAiImageService $imageService)
    {
        $this->authorizeOwner($scene->project);

        try {
            $prompt = $scene->image_prompt ?: $scene->dialogue;
            $imagePath = $imageService->generateImage($prompt);

            $scene->update([
                'image_path' => $imagePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã sinh Ảnh thử thành công.',
                'scene' => $scene->fresh(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi sinh Ảnh thử: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function startRender(Project $project, CreatomateRenderService $renderService, ElevenLabsService $voiceService, FalAiImageService $imageService)
    {
        $this->authorizeOwner($project);

        $scenes = $project->scenes()->orderBy('scene_index', 'asc')->get();

        if ($scenes->isEmpty()) {
            return response()->json(['message' => 'Dự án chưa có phân cảnh nào để render.'], 422);
        }

        // Ensure missing assets are generated before rendering
        foreach ($scenes as $scene) {
            if (empty($scene->audio_path)) {
                $voiceId = $scene->voice_id ?: '21m00Tcm4TlvDq8ikWAM';
                $voiceRes = $voiceService->generateVoice($scene->dialogue, $voiceId);
                $scene->update(['audio_path' => $voiceRes['audio_path'], 'duration' => $voiceRes['duration']]);
            }
            if (empty($scene->image_path)) {
                $prompt = $scene->image_prompt ?: $scene->dialogue;
                $imgPath = $imageService->generateImage($prompt);
                $scene->update(['image_path' => $imgPath]);
            }
        }

        try {
            $renderJobId = $renderService->renderProjectVideo($project);

            $project->update([
                'status' => 'rendering',
                'render_job_id' => $renderJobId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã khởi chạy Batch Render Video Hoàn Chỉnh!',
                'project' => $project->fresh(['scenes']),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi khởi chạy Render Video: ' . $e->getMessage(),
            ], 422);
        }
    }

    private function authorizeOwner(Project $project): void
    {
        abort_if($project->user_id !== auth()->id(), 403);
    }
}
