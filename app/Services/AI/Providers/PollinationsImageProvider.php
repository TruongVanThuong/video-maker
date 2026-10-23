<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\ImageGeneratorInterface;
use App\Services\AI\DTOs\GeneratedImageResult;
use App\Services\AI\Support\MediaStorageWriter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class PollinationsImageProvider implements ImageGeneratorInterface
{
    private const TIMEOUT_SECONDS = 40;

    private const MAX_RETRIES = 2;

    private const RETRY_DELAY_MS = 500;

    public function __construct(
        private readonly int $width = 768,
        private readonly int $height = 432,
        private readonly string $disk = 'public',
    ) {}

    public function generate(string $prompt): GeneratedImageResult
    {
        if (trim($prompt) === '') {
            throw new RuntimeException('Prompt hình ảnh không được để trống.');
        }

        $imageUrl = 'https://image.pollinations.ai/prompt/'.rawurlencode($prompt).'?'
            .http_build_query([
                'width' => $this->width,
                'height' => $this->height,
                'nologo' => 'true',
                'seed' => random_int(1, 1_000_000),
            ]);

        $response = Http::timeout(self::TIMEOUT_SECONDS)
            ->retry(self::MAX_RETRIES, self::RETRY_DELAY_MS, throw: false)
            ->get($imageUrl);

        if ($response->failed()) {
            throw new RuntimeException('Pollinations image API error ('.$response->status().'): '.$response->body());
        }

        $path = 'images/'.Str::uuid()->toString().'.png';

        MediaStorageWriter::store(
            $this->disk,
            $path,
            $response->body(),
            ['image/png', 'image/jpeg', 'image/webp', 'image/avif', 'image/gif'],
        );

        return new GeneratedImageResult(path: $path, provider: 'pollinations');
    }
}
