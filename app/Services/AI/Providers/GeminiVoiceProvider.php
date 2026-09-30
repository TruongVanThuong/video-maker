<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\VoiceGeneratorInterface;
use App\Services\AI\DTOs\GeneratedVoiceResult;
use App\Services\AI\Support\L16PcmToWav;
use App\Services\AI\Support\MediaStorageWriter;
use App\Services\AI\Support\SpeechDuration;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class GeminiVoiceProvider implements VoiceGeneratorInterface
{
    private const TIMEOUT_SECONDS = 60;

    private const MAX_RETRIES = 2;

    private const RETRY_DELAY_MS = 2000;

    private const MODEL = 'gemini-2.5-flash-preview-tts';

    /**
     * @var string[]
     */
    private const KNOWN_VOICES = ['Kore', 'Puck', 'Charon', 'Fenrir', 'Leda', 'Orus', 'Zephyr', 'Aoede'];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $defaultVoice = 'Kore',
        private readonly string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta',
        private readonly string $disk = 'public',
    ) {}

    public function generate(string $text, ?string $voiceId = null): GeneratedVoiceResult
    {
        if (trim($text) === '') {
            throw new RuntimeException('Text to speech cannot be empty.');
        }

        $response = Http::withHeaders([
            'X-goog-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(self::TIMEOUT_SECONDS)
            ->retry(self::MAX_RETRIES, self::RETRY_DELAY_MS, function (Throwable $e) {
                if ($e instanceof ConnectionException) {
                    return true;
                }

                return $e instanceof RequestException
                    && in_array($e->response->status(), [429, 500, 502, 503], true);
            })
            ->post("{$this->baseUrl}/models/".self::MODEL.':generateContent', [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [
                            ['text' => $text],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'speechConfig' => [
                        'voiceConfig' => [
                            'prebuiltVoiceConfig' => [
                                'voiceName' => $this->resolveVoice($voiceId),
                            ],
                        ],
                    ],
                    'responseModalities' => ['AUDIO'],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gemini TTS API error ('.$response->status().'): '.$response->body());
        }

        $parts = $response->json('candidates.0.content.parts') ?? [];

        $inlineData = null;
        foreach ($parts as $part) {
            if (isset($part['inlineData']['data'])) {
                $inlineData = $part['inlineData'];
                break;
            }
        }

        if ($inlineData === null) {
            throw new RuntimeException('Gemini TTS returned an unexpected response shape: '.$response->body());
        }

        $sampleRate = self::extractSampleRate($inlineData['mimeType'] ?? '');
        $pcm = base64_decode($inlineData['data'], true);

        if ($pcm === false || $pcm === '') {
            throw new RuntimeException('Gemini TTS returned empty or invalid audio payload.');
        }

        $wav = L16PcmToWav::convert($pcm, $sampleRate);
        $duration = SpeechDuration::fromPcmLength(strlen($pcm), $sampleRate);

        $path = 'audio/'.Str::uuid()->toString().'.wav';

        MediaStorageWriter::store(
            $this->disk,
            $path,
            $wav,
            ['audio/wav', 'audio/x-wav', 'audio/vnd.wave'],
        );

        return new GeneratedVoiceResult(
            path: $path,
            durationSeconds: $duration,
            provider: 'gemini-tts',
            mimeType: 'audio/wav',
        );
    }

    private function resolveVoice(?string $voiceId): string
    {
        if ($voiceId !== null && in_array($voiceId, self::KNOWN_VOICES, true)) {
            return $voiceId;
        }

        return in_array($this->defaultVoice, self::KNOWN_VOICES, true)
            ? $this->defaultVoice
            : self::KNOWN_VOICES[0];
    }

    private static function extractSampleRate(string $mimeType): int
    {
        if (preg_match('/rate=(\d+)/', $mimeType, $matches)) {
            return (int) $matches[1];
        }

        return 24000;
    }
}
