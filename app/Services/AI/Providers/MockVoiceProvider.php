<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\VoiceGeneratorInterface;
use App\Services\AI\DTOs\GeneratedVoiceResult;
use App\Services\AI\Support\L16PcmToWav;
use App\Services\AI\Support\MediaStorageWriter;
use App\Services\AI\Support\SpeechDuration;
use Illuminate\Support\Str;

final class MockVoiceProvider implements VoiceGeneratorInterface
{
    public function __construct(private readonly string $disk = 'public') {}

    public function generate(string $text, ?string $voiceId = null): GeneratedVoiceResult
    {
        $duration = SpeechDuration::estimateFromText($text);
        $silencePcm = str_repeat("\0", (int) round($duration * 24000) * 2);
        $wav = L16PcmToWav::convert($silencePcm, 24000);

        $path = 'audio/'.Str::uuid()->toString().'.wav';

        MediaStorageWriter::store(
            $this->disk,
            $path,
            $wav,
            ['audio/wav', 'audio/x-wav'],
        );

        return new GeneratedVoiceResult(
            path: $path,
            durationSeconds: $duration,
            provider: 'mock',
            mimeType: 'audio/wav',
        );
    }
}
