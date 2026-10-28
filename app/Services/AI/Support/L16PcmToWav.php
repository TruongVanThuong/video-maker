<?php

declare(strict_types=1);

namespace App\Services\AI\Support;

final class L16PcmToWav
{
    /**
     * Wrap raw 16-bit little-endian PCM samples into a standard RIFF/WAVE container.
     */
    public static function convert(string $pcm, int $sampleRate = 24000, int $channels = 1): string
    {
        $dataSize = strlen($pcm);
        $blockAlign = $channels * 2;
        $byteRate = $sampleRate * $blockAlign;

        $header = 'RIFF'
            .pack('V', 36 + $dataSize)
            .'WAVE'
            .'fmt '
            .pack('V', 16)          // Subchunk1Size
            .pack('v', 1)           // AudioFormat: PCM
            .pack('v', $channels)   // NumChannels
            .pack('V', $sampleRate) // SampleRate
            .pack('V', $byteRate)   // ByteRate
            .pack('v', $blockAlign) // BlockAlign
            .pack('v', 16)          // BitsPerSample
            .'data'
            .pack('V', $dataSize);

        return $header.$pcm;
    }
}
