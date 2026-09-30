<?php

declare(strict_types=1);

namespace Tests\Unit\AI;

use App\Services\AI\Support\L16PcmToWav;
use PHPUnit\Framework\TestCase;

class L16PcmToWavTest extends TestCase
{
    public function test_it_builds_a_valid_wav_container(): void
    {
        $pcm = str_repeat("\x00\x00", 100);

        $wav = L16PcmToWav::convert($pcm, 24000, 1);

        $this->assertSame('RIFF', substr($wav, 0, 4));
        $this->assertSame('WAVE', substr($wav, 8, 4));
        $this->assertSame('fmt ', substr($wav, 12, 4));
        $this->assertSame('data', substr($wav, 36, 4));

        $this->assertSame(44 + 200, strlen($wav));
        $this->assertSame(24000, unpack('V', substr($wav, 24, 4))[1]);
        $this->assertSame(1, unpack('v', substr($wav, 22, 2))[1]);
        $this->assertSame(16, unpack('v', substr($wav, 34, 2))[1]);
        $this->assertSame(200, unpack('V', substr($wav, 40, 4))[1]);
    }
}
