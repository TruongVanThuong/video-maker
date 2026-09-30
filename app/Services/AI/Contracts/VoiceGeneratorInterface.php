<?php

declare(strict_types=1);

namespace App\Services\AI\Contracts;

use App\Services\AI\DTOs\GeneratedVoiceResult;

interface VoiceGeneratorInterface
{
    public function generate(string $text, ?string $voiceId = null): GeneratedVoiceResult;
}
