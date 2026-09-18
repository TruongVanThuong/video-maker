<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ElevenLabsService
{
    private ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.elevenlabs.key', env('ELEVENLABS_API_KEY'));
    }

    /**
     * Synthesize text to speech using ElevenLabs API and save to storage.
     *
     * @return array{audio_path: string, duration: float}
     */
    public function generateVoice(string $text, string $voiceId = '21m00Tcm4TlvDq8ikWAM'): array
    {
        if (empty($text)) {
            throw new RuntimeException('Text to speech cannot be empty.');
        }

        $filename = 'audio/' . Str::uuid()->toString() . '.mp3';

        // Real API Call if Key Present
        if (!empty($this->apiKey)) {
            $response = Http::withHeaders([
                'xi-api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'audio/mpeg',
            ])->post("https://api.elevenlabs.io/v1/text-to-speech/{$voiceId}", [
                'text' => $text,
                'model_id' => 'eleven_multilingual_v2',
                'voice_settings' => [
                    'stability' => 0.5,
                    'similarity_boost' => 0.75,
                ],
            ]);

            if ($response->failed()) {
                throw new RuntimeException('ElevenLabs API Error: ' . $response->body());
            }

            Storage::disk('public')->put($filename, $response->body());
            $duration = $this->calculateDuration($text);

            return [
                'audio_path' => Storage::disk('public')->url($filename),
                'duration' => $duration,
            ];
        }

        // Mock / Development Fallback audio asset
        $dummyMp3Content = base64_decode('SUQzBAAAAAAAIFRJVDIAAAANAAADTGF2ZjU4Ljc2LjEwMAD/70DEAAAAAABAAAAA');
        Storage::disk('public')->put($filename, $dummyMp3Content);

        $wordCount = str_word_count($text);
        $estimatedDuration = max(2.5, round($wordCount / 2.5, 2));

        return [
            'audio_path' => Storage::disk('public')->url($filename),
            'duration' => $estimatedDuration,
        ];
    }

    private function calculateDuration(string $text): float
    {
        $words = count(preg_split('/\s+/', trim($text)) ?: []);
        return max(2.0, round($words / 2.5, 2));
    }
}
