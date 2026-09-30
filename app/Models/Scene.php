<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
     * Tự động serialize `audio_url` / `image_url` vào JSON / Inertia responses.
     */
    protected $appends = [
        'audio_url',
        'image_url',
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

    /**
     * URL công khai của audio đã sinh (audio_path lưu relative path).
     */
    public function getAudioUrlAttribute(): ?string
    {
        return $this->resolvePublicUrl($this->audio_path);
    }

    /**
     * URL công khai của ảnh đã sinh (image_path lưu relative path).
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->resolvePublicUrl($this->image_path);
    }

    private function resolvePublicUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Legacy data (hoặc external URL) đã lưu URL tuyệt đối sẵn.
        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
