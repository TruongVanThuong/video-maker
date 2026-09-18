<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ContentPrompt;
use App\Models\Project;
use App\Models\Scene;
use Illuminate\Support\Str;
use RuntimeException;

class ConvertContentPromptToProjectAction
{
    public function execute(ContentPrompt $contentPrompt): Project
    {
        if (!$contentPrompt->isCompleted()) {
            throw new RuntimeException('Chỉ có thể chuyển đổi khi Prompt ở trạng thái hoàn thành.');
        }

        $structure = $contentPrompt->analyzed_structure ?? [];
        $title = $structure['subject'] ?? Str::limit($contentPrompt->input_content, 40);
        $title = 'Dự án: ' . Str::headline($title);

        $project = Project::create([
            'user_id' => $contentPrompt->user_id,
            'content_prompt_id' => $contentPrompt->id,
            'title' => $title,
            'source_type' => 'story',
            'raw_input' => $contentPrompt->input_content,
            'status' => 'draft',
        ]);

        $this->createScenes($project, $contentPrompt, $structure);

        $contentPrompt->update([
            'conversion_status' => 'converted',
            'project_id' => $project->id,
        ]);

        return $project->fresh(['scenes']);
    }

    private function createScenes(Project $project, ContentPrompt $contentPrompt, array $structure): void
    {
        if (!empty($structure['scenes']) && is_array($structure['scenes'])) {
            foreach ($structure['scenes'] as $index => $sceneData) {
                Scene::create([
                    'project_id' => $project->id,
                    'scene_index' => $index + 1,
                    'dialogue' => $sceneData['dialogue'] ?? $sceneData['action'] ?? $contentPrompt->input_content,
                    'voice_id' => $sceneData['voice_id'] ?? '21m00Tcm4TlvDq8ikWAM', // Default Rachel Voice
                    'image_prompt' => $sceneData['final_video_prompt'] ?? $sceneData['image_prompt'] ?? $contentPrompt->final_prompt,
                    'camera_rule' => [
                        'motion' => $sceneData['camera_movement'] ?? 'pan_right',
                        'speed' => 'normal',
                        'zoom' => 'in',
                    ],
                ]);
            }
            return;
        }

        // Default: Create at least Scene 1 based on current analyzed_structure
        Scene::create([
            'project_id' => $project->id,
            'scene_index' => 1,
            'dialogue' => $structure['action'] ?? $contentPrompt->input_content,
            'voice_id' => '21m00Tcm4TlvDq8ikWAM', // Rachel
            'image_prompt' => $contentPrompt->final_prompt ?? ($structure['environment'] ?? '') . ', ' . ($structure['subject'] ?? ''),
            'camera_rule' => [
                'motion' => $structure['camera_movement'] ?? 'pan_right',
                'speed' => 'normal',
                'zoom' => 'in',
            ],
        ]);
    }
}
