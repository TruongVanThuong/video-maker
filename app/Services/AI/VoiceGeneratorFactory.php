<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Contracts\VoiceGeneratorInterface;
use App\Services\AI\Providers\GeminiVoiceProvider;
use App\Services\AI\Providers\MockVoiceProvider;
use RuntimeException;

final class VoiceGeneratorFactory
{
    public static function make(): VoiceGeneratorInterface
    {
        $geminiKey = config('services.gemini.key');

        if (! empty($geminiKey)) {
            return new GeminiVoiceProvider(
                apiKey: $geminiKey,
                defaultVoice: (string) config('services.gemini.tts_default_voice', 'Kore'),
                baseUrl: (string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1beta'),
            );
        }

        if ((bool) config('services.ai.demo_mode')) {
            return new MockVoiceProvider;
        }

        throw new RuntimeException(
            'No voice provider configured: GEMINI_API_KEY is missing and demo mode is disabled.'
        );
    }
}
