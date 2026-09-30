<?php

declare(strict_types=1);

namespace App\Services\AI\Contracts;

use App\Services\AI\DTOs\GeneratedImageResult;

interface ImageGeneratorInterface
{
    public function generate(string $prompt): GeneratedImageResult;
}
