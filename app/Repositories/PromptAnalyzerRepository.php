<?php

namespace App\Repositories;

use App\Models\PromptTemplate;
use Illuminate\Pagination\LengthAwarePaginator;

class PromptAnalyzerRepository
{
    /**
     * The PromptTemplate model instance.
     */
    protected PromptTemplate $model;

    /**
     * PromptAnalyzerRepository constructor.
     */
    public function __construct(PromptTemplate $model)
    {
        $this->model = $model;
    }

    /**
     * Get all prompt templates by params
     */
    public function getAll($onlyRoot = false): LengthAwarePaginator
    {
        $query = $this->model->with('childrenRecursive');

        if ($onlyRoot) {
            $query->whereNull('parent_id');
        }

        return $query->orderBy('id', 'desc')->paginate(10);
    }

    /**
     * Find PromptTemplate by ID
     */
    public function find(int $id): ?PromptTemplate
    {
        return $this->model->find($id);
    }

    /**
     * Create new PromptTemplate
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PromptTemplate
    {
        return $this->model->create($data);
    }

    /**
     * Update PromptTemplate by ID
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): bool
    {
        $promptTemplate = $this->model->findOrFail($id);

        return $promptTemplate->update($data);
    }

    /**
     * Delete PromptTemplate by ID
     *
     *
     * @throws \Exception
     */
    public function delete(int $id): ?bool
    {
        $promptTemplate = $this->model->findOrFail($id);
        $promptTemplate->children()->delete();

        return $promptTemplate->delete();
    }
}
