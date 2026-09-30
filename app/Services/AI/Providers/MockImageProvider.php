<?php

declare(strict_types=1);

namespace App\Services\AI\Providers;

use App\Services\AI\Contracts\ImageGeneratorInterface;
use App\Services\AI\DTOs\GeneratedImageResult;
use App\Services\AI\Support\MediaStorageWriter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class MockImageProvider implements ImageGeneratorInterface
{
    public function __construct(private readonly string $disk = 'public') {}

    public function generate(string $prompt): GeneratedImageResult
    {
        $mockImageUrl = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe'
            .'?auto=format&fit=crop&w=1200&q=80&sig='.random_int(1, 1_000_000);

        $binary = Http::timeout(10)->get($mockImageUrl);

        if ($binary->failed()) {
            throw new RuntimeException('Failed to download mock placeholder image ('.$binary->status().').');
        }

        $path = 'images/'.Str::uuid()->toString().'.png';

        try {
            MediaStorageWriter::store(
                $this->disk,
                $path,
                $binary->body(),
                ['image/jpeg', 'image/png', 'image/webp'],
            );
        } catch (\Throwable $e) {
            report($e);

            throw new RuntimeException('Mock image provider failed to store fallback image.', previous: $e);
        }

        return new GeneratedImageResult(path: $path, provider: 'mock');
    }
}
