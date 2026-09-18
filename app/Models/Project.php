<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    /**
     * Các thuộc tính có thể fill hàng loạt (Mass Assignable).
     */
    protected $fillable = [
        'user_id',
        'content_prompt_id',
        'title',
        'source_type',
        'raw_input',
        'status',
        'batch_id',
        'render_job_id',
    ];

    /**
     * Mối quan hệ: Một Dự án thuộc về một Người dùng.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mối quan hệ: Dự án có thể được chuyển đổi từ một ContentPrompt.
     */
    public function contentPrompt(): BelongsTo
    {
        return $this->belongsTo(ContentPrompt::class);
    }

    /**
     * Mối quan hệ: Một Dự án có nhiều Phân cảnh (Ordered theo scene_index).
     */
    public function scenes(): HasMany
    {
        return $this->hasMany(Scene::class)->orderBy('scene_index', 'asc');
    }

    /**
     * Mối quan hệ: Một Dự án có một Thành phẩm Media cuối cùng.
     */
    public function media(): HasOne
    {
        return $this->hasOne(ProjectMedia::class);
    }

    /**
     * Mối quan hệ: Một Dự án có nhiều lịch sử gọi API AI.
     */
    public function aiJobs(): HasMany
    {
        return $this->hasMany(AiJob::class);
    }
}