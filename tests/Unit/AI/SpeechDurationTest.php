<?php

declare(strict_types=1);

namespace Tests\Unit\AI;

use App\Services\AI\Support\SpeechDuration;
use PHPUnit\Framework\TestCase;

class SpeechDurationTest extends TestCase
{
    public function test_it_estimates_duration_from_word_count(): void
    {
        $this->assertSame(2.5, SpeechDuration::estimateFromText(''));
        // "Xin chào tạm biệt" = 2 từ -> 0.8s -> bị chặn ở min 2.5s
        $this->assertSame(2.5, SpeechDuration::estimateFromText('Xin chào tạm biệt'));
        // 10 từ -> 4.0s
        $this->assertSame(4.0, SpeechDuration::estimateFromText('một hai ba bốn năm sáu bảy tám chín mười'));
        // Giới hạn trên 10s
        $this->assertSame(10.0, SpeechDuration::estimateFromText(str_repeat('từ ', 100)));
    }

    public function test_it_computes_duration_from_pcm_bytes(): void
    {
        $this->assertSame(4.0, SpeechDuration::fromPcmLength(24000 * 2 * 4, 24000));
        $this->assertSame(1.0, SpeechDuration::fromPcmLength(48000, 24000));
    }
}
