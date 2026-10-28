<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Models\AiJob;
use App\Models\Scene;
use Throwable;

final class AiJobLogger
{
    public static function record(
        Scene $scene,
        string $jobType,
        string $provider,
        string $status = 'success',
        float $apiCost = 0.0,
        ?array $responseData = null,
        ?string $errorMessage = null,
    ): AiJob {
        return AiJob::create([
            'project_id' => $scene->project_id,
            'scene_id' => $scene->id,
            'job_type' => $jobType,
            'provider' => $provider,
            'status' => $status,
            'api_cost' => $apiCost,
            'response_data' => $responseData,
            'error_message' => $errorMessage,
        ]);
    }

    public static function recordFailure(Scene $scene, string $jobType, string $provider, Throwable $e): AiJob
    {
        return self::record(
            scene: $scene,
            jobType: $jobType,
            provider: $provider,
            status: 'failed',
            responseData: null,
            errorMessage: $e->getMessage(),
        );
    }
}
