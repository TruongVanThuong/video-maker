<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiJob extends Model
{
    protected $fillable = [
        'project_id',
        'scene_id',
        'job_type',
        'provider',
        'status',
        'api_cost',
        'response_data',
        'error_message',
    ];

    protected $casts = [
        'response_data' => 'array',
        'api_cost' => 'float',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function scene(): BelongsTo
    {
        return $this->belongsTo(Scene::class);
    }
}