<?php

namespace App\Repositories;

use App\Models\ContentPrompt;
use Illuminate\Database\Eloquent\Collection;

class ContentPromptRepository
{
    protected ContentPrompt $model;

    public function __construct(ContentPrompt $model)
    {
        $this->model = $model;
    }

    public function create(array $data): ContentPrompt
    {
        return $this->model->create($data);
    }

    public function find(int $id): ?ContentPrompt
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): ContentPrompt
    {
        return $this->model->findOrFail($id);
    }

    public function findByUser(int $userId, int $limit = 10): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->latest()
            ->take($limit)
            ->get();
    }

    public function updateStatus(int $id, string $status, ?string $errorMessage = null): bool
    {
        $data = ['status' => $status];

        if ($errorMessage !== null) {
            $data['error_message'] = $errorMessage;
        }

        return $this->model->findOrFail($id)->update($data);
    }

    public function markAsProcessing(int $id): bool
    {
        return $this->updateStatus($id, 'processing');
    }

    public function markAsCompleted(int $id, array $result): bool
    {
        return $this->model->findOrFail($id)->update([
            'status' => 'completed',
            'analyzed_structure' => $result,
            'final_prompt' => $result['final_video_prompt'] ?? null,
        ]);
    }

    public function markAsFailed(int $id, string $errorMessage): bool
    {
        return $this->updateStatus($id, 'failed', $errorMessage);
    }

    public function applyManualEdit(int $id, array $fields, ?string $finalPrompt = null): bool
    {
        $contentPrompt = $this->model->findOrFail($id);

        $analyzedStructure = array_merge(
            $contentPrompt->analyzed_structure ?? [],
            $fields
        );

        return $contentPrompt->update([
            'analyzed_structure' => $analyzedStructure,
            'final_prompt' => $finalPrompt ?? $contentPrompt->final_prompt,
            'is_manually_edited' => true,
        ]);
    }

    public function markAsRefined(int $id, array $result, int $refinementCount): bool
    {
        return $this->model->findOrFail($id)->update([
            'status' => ContentPrompt::STATUS_COMPLETED,
            'analyzed_structure' => $result,
            'final_prompt' => $result['final_video_prompt'] ?? null,
            'refinement_count' => $refinementCount,
            'is_manually_edited' => false, // refine mới ghi đè -> reset cờ manual
        ]);
    }
}
