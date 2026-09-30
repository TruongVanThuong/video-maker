<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\ImageGeneratorInterface;
use App\Services\AI\DTOs\GeneratedImageResult;
use App\Services\AI\Support\MediaStorageWriter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class FalAiImageProvider implements ImageGeneratorInterface
{
    private const TIMEOUT_SECONDS = 40;

    private const MAX_RETRIES = 2;

    private const RETRY_DELAY_MS = 1000;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'fal-ai/flux/schnell',
        private readonly string $disk = 'public',
    ) {}

    public function generate(string $prompt): GeneratedImageResult
    {
        if (trim($prompt) === '') {
            throw new RuntimeException('Image prompt cannot be empty.');
        }

        $response = Http::withHeaders([
            'Authorization' => 'Key '.$this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout(self::TIMEOUT_SECONDS)
            ->retry(self::MAX_RETRIES, self::RETRY_DELAY_MS, throw: false)
            ->post('https://fal.run/'.$this->model, [
                'prompt' => $prompt,
                'image_size' => 'landscape_16_9',
                'num_inference_steps' => 4,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Fal.ai API error ('.$response->status().'): '.$response->body());
        }

        $imageUrl = $response->json('images.0.url');

        if (empty($imageUrl)) {
            throw new RuntimeException('Fal.ai returned an unexpected response shape: '.$response->body());
        }

        $imageBinary = Http::timeout(self::TIMEOUT_SECONDS)->get($imageUrl);

        if ($imageBinary->failed()) {
            throw new RuntimeException('Failed to download generated image from Fal.ai ('.$imageBinary->status().').');
        }

        $path = 'images/'.Str::uuid()->toString().'.png';

        MediaStorageWriter::store(
            $this->disk,
            $path,
            $imageBinary->body(),
            ['image/png', 'image/jpeg', 'image/webp'],
        );

        return new GeneratedImageResult(path: $path, provider: 'fal-ai', mimeType: 'image/png');
    }
}
