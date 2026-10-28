<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMedia extends Model
{
    // Chỉ định rõ tên bảng vì có chứa ký tự số nhiều phức tạp
    protected $table = 'project_medias';

    protected $fillable = [
        'project_id',
        'media_type',
        'file_url',
        'file_path',
        'file_size',
        'total_duration',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'total_duration' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}