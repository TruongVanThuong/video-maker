<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPrompt extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const MAX_REFINEMENTS = 5;

    protected $fillable = [
        'user_id',
        'prompt_template_id',
        'target_platform',
        'input_content',
        'analyzed_structure',
        'final_prompt',
        'status',
        'conversion_status',
        'project_id',
        'error_message',
        'refinement_count',
        'is_manually_edited',
    ];

    protected $casts = [
        'analyzed_structure' => 'array',
        'is_manually_edited' => 'boolean',
    ];

    public function canBeRefined(): bool
    {
        return $this->isCompleted() && $this->refinement_count < self::MAX_REFINEMENTS;
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(
            PromptTemplate::class,
            'prompt_template_id'
        );
    }

    public function projects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Project::class, 'content_prompt_id');
    }

    public function latestProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function scopeOfUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}
