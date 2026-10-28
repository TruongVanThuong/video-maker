<?php

namespace App\Repositories;

use App\Models\PromptTemplate;
use Illuminate\Pagination\LengthAwarePaginator;

class PromptTemplateRepository
{
    protected PromptTemplate $model;

    public function __construct(PromptTemplate $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated prompt templates with filters and usage count
     */
    public function getAll(
        ?string $category = null,
        ?string $search = null,
        bool $onlyActive = false,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = $this->model->withCount('contentPrompts');

        if ($onlyActive) {
            $query->where('is_active', true);
        }

        if (!empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('system_instruction', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();
    }

    /**
     * Get distinct categories from templates
     */
    public function getCategories(): array
    {
        return $this->model
            ->pluck('category')
            ->unique()
            ->values()
            ->toArray();
    }

    public function find(int $id): ?PromptTemplate
    {
        return $this->model->find($id);
    }

    public function findOrFail(int $id): PromptTemplate
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): PromptTemplate
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        $promptTemplate = $this->model->findOrFail($id);
        return $promptTemplate->update($data);
    }

    public function duplicate(int $id): PromptTemplate
    {
        $original = $this->model->findOrFail($id);
        
        return $this->model->create([
            'name' => 'Copy of ' . $original->name,
            'category' => $original->category,
            'system_instruction' => $original->system_instruction,
            'is_active' => $original->is_active,
        ]);
    }

    public function toggleStatus(int $id): bool
    {
        $template = $this->model->findOrFail($id);
        return $template->update(['is_active' => !$template->is_active]);
    }

    public function delete(int $id): ?bool
    {
        $promptTemplate = $this->model->findOrFail($id);
        $promptTemplate->contentPrompts()->delete();
        return $promptTemplate->delete();
    }
}
