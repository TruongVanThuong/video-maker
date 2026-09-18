<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Scene;
use App\Services\ElevenLabsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSceneVoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Scene $scene
    ) {}

    public function handle(ElevenLabsService $voiceService): void
    {
        $voiceId = $this->scene->voice_id ?: '21m00Tcm4TlvDq8ikWAM';
        $result = $voiceService->generateVoice($this->scene->dialogue, $voiceId);

        $this->scene->update([
            'audio_path' => $result['audio_path'],
            'duration' => $result['duration'],
        ]);
    }
}
