<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scene extends Model
{
    protected $fillable = [
        'project_id',
        'scene_index',
        'dialogue',
        'voice_id',
        'image_prompt',
        'camera_rule',
        'audio_path',
        'image_path',
        'duration',
    ];

    /**
     * Ép kiểu dữ liệu (Casting) tự động khi truy vấn từ DB.
     */
    protected $casts = [
        'camera_rule' => 'array', // Tự động convert JSON từ DB sang Array trong PHP
        'duration' => 'float',
        'scene_index' => 'integer',
    ];

    /**
     * Mối quan hệ: Một Phân cảnh thuộc về một Dự án.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Mối quan hệ: Một Phân cảnh có nhiều lịch sử gọi API AI (Voice/Image cụ thể).
     */
    public function aiJobs(): HasMany
    {
        return $this->hasMany(AiJob::class);
    }
}