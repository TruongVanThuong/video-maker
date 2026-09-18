<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Scene;
use App\Services\FalAiImageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSceneImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Scene $scene
    ) {}

    public function handle(FalAiImageService $imageService): void
    {
        $prompt = $this->scene->image_prompt ?: $this->scene->dialogue;
        $imagePath = $imageService->generateImage($prompt);

        $this->scene->update([
            'image_path' => $imagePath,
        ]);
    }
}
