<?php

declare(strict_types=1);

namespace App\Services\AI;

use App\Services\AI\Contracts\ImageGeneratorInterface;
use App\Services\AI\Providers\FalAiImageProvider;
use App\Services\AI\Providers\MockImageProvider;
use App\Services\AI\Providers\PollinationsImageProvider;

final class ImageGeneratorFactory
{
    public static function make(): ImageGeneratorInterface
    {
        $falKey = config('services.falai.key');
        $falEnabled = (bool) config('services.falai.enabled');

        if ($falEnabled && ! empty($falKey)) {
            return new FalAiImageProvider(apiKey: $falKey);
        }

        if ((bool) config('services.ai.demo_mode')) {
            return new MockImageProvider;
        }

        return new PollinationsImageProvider;
    }
}
