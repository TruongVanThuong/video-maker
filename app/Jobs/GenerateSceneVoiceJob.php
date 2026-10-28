<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Scene;
use App\Services\SceneAssetGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSceneVoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public Scene $scene
    ) {}

    public function handle(SceneAssetGenerator $assetGenerator): void
    {
        $assetGenerator->ensureVoice($this->scene);
    }
}
