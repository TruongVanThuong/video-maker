<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Scene;
use App\Services\AI\AiJobLogger;
use App\Services\AI\ImageGeneratorFactory;
use App\Services\AI\VoiceGeneratorFactory;
use Throwable;

class SceneAssetGenerator
{
    /**
     * Ensure a scene has a generated voice-over. Stores the RELATIVE path
     * (e.g. `audio/xxx.wav`) so it stays portable across environments.
     */
    public function ensureVoice(Scene $scene): void
    {
        if (! empty($scene->audio_path)) {
            return;
        }

        if (trim((string) $scene->dialogue) === '') {
            throw new \RuntimeException('Cannot generate voice for a scene without dialogue.');
        }

        try {
            $voiceId = $scene->voice_id ?: config('services.gemini.tts_default_voice', 'Kore');
            $result = VoiceGeneratorFactory::make()->generate($scene->dialogue, $voiceId);

            $scene->update([
                'audio_path' => $result->path,
                'duration' => $result->durationSeconds,
            ]);

            AiJobLogger::record(
                scene: $scene,
                jobType: 'generate_voice',
                provider: $result->provider,
                responseData: [
                    'path' => $result->path,
                    'duration_seconds' => $result->durationSeconds,
                    'mime_type' => $result->mimeType,
                ],
            );
        } catch (Throwable $e) {
            AiJobLogger::recordFailure($scene, 'generate_voice', $scene->voice_id ?: 'gemini-tts', $e);

            throw $e;
        }
    }

    /**
     * Ensure a scene has a generated image. Stores the RELATIVE path
     * (e.g. `images/xxx.png`) so it stays portable across environments.
     */
    public function ensureImage(Scene $scene): void
    {
        if (! empty($scene->image_path)) {
            return;
        }

        try {
            $prompt = $scene->image_prompt ?: $scene->dialogue;
            $result = ImageGeneratorFactory::make()->generate($prompt);

            $scene->update([
                'image_path' => $result->path,
            ]);

            AiJobLogger::record(
                scene: $scene,
                jobType: 'generate_image',
                provider: $result->provider,
                responseData: [
                    'path' => $result->path,
                    'mime_type' => $result->mimeType,
                ],
            );
        } catch (Throwable $e) {
            AiJobLogger::recordFailure($scene, 'generate_image', 'pollinations', $e);

            throw $e;
        }
    }
}
