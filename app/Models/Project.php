<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_RENDERING = 'rendering';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

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
        'video_path',
        'rendered_at',
        'render_error',
    ];

    /**
     * Tự động serialize `video_url` vào JSON / Inertia responses.
     */
    protected $appends = [
        'video_url',
    ];

    protected $casts = [
        'rendered_at' => 'datetime',
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

    /**
     * URL công khai của video hoàn chỉnh (video_path lưu relative path hoặc đường dẫn tuyệt đối).
     */
    public function getVideoUrlAttribute(): ?string
    {
        $path = $this->video_path;

        if (empty($path)) {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
