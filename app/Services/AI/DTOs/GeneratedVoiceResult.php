<?php

declare(strict_types=1);

namespace App\Services\AI\DTOs;

final class GeneratedVoiceResult
{
    public function __construct(
        public readonly string $path,
        public readonly float $durationSeconds,
        public readonly string $provider,
        public readonly ?string $mimeType = null,
    ) {}
}
