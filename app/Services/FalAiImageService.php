<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class FalAiImageService
{
    private ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.falai.key', env('FAL_KEY'));
    }

    /**
     * Generate an AI image from prompt and save to storage.
     *
     * @return string Image storage path or URL
     */
    public function generateImage(string $prompt): string
    {
        if (empty($prompt)) {
            throw new RuntimeException('Image prompt cannot be empty.');
        }

        $filename = 'images/' . Str::uuid()->toString() . '.png';

        if (!empty($this->apiKey)) {
            $response = Http::withHeaders([
                'Authorization' => 'Key ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://fal.run/fal-ai/flux/schnell', [
                'prompt' => $prompt,
                'image_size' => 'landscape_16_9',
                'num_inference_steps' => 4,
            ]);

            if ($response->failed()) {
                throw new RuntimeException('Fal.ai API Error: ' . $response->body());
            }

            $imageUrl = $response->json('images.0.url');
            if ($imageUrl) {
                $imageBinary = Http::get($imageUrl)->body();
                Storage::disk('public')->put($filename, $imageBinary);
                return Storage::disk('public')->url($filename);
            }
        }

        // Mock / Demo High Quality Placeholder Image
        $encodedPrompt = urlencode(Str::limit($prompt, 30));
        $mockImageUrl = "https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80&sig=" . rand(1, 1000);
        
        try {
            $binary = Http::timeout(10)->get($mockImageUrl)->body();
            Storage::disk('public')->put($filename, $binary);
            return Storage::disk('public')->url($filename);
        } catch (\Throwable $e) {
            return $mockImageUrl;
        }
    }
}
