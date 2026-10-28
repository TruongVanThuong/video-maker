<?php

declare(strict_types=1);

namespace App\Services\AI\Support;

final class SpeechDuration
{
    public const WORDS_PER_SECOND = 2.5;

    public const MIN_SECONDS = 2.5;

    public const MAX_SECONDS = 10.0;

    public static function estimateFromText(string $text): float
    {
        $words = count(preg_split('/\s+/u', trim($text)) ?: []);

        return min(
            self::MAX_SECONDS,
            max(self::MIN_SECONDS, round($words / self::WORDS_PER_SECOND, 2)),
        );
    }

    public static function fromPcmLength(int $byteLength, int $sampleRate, int $channels = 1): float
    {
        $samplesPerChannel = (int) ($byteLength / 2 / $channels);

        return round($samplesPerChannel / $sampleRate, 2);
    }
}
