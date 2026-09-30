<?php

declare(strict_types=1);

namespace Tests\Unit\AI\Providers;

use App\Services\AI\Providers\PollinationsImageProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class PollinationsImageProviderTest extends TestCase
{
    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function test_it_generates_and_stores_an_image(): void
    {
        Storage::fake('public');

        Http::fake([
            'image.pollinations.ai/*' => Http::response(base64_decode(self::PNG_1X1), 200, [
                'Content-Type' => 'image/png',
            ]),
        ]);

        $result = (new PollinationsImageProvider)->generate('a red panda');

        $this->assertSame('pollinations', $result->provider);
        $this->assertStringStartsWith('images/', $result->path);

        Storage::disk('public')->assertExists($result->path);
    }

    public function test_it_throws_when_the_api_fails(): void
    {
        Http::fake([
            'image.pollinations.ai/*' => Http::response('service unavailable', 503),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Pollinations image API error');

        (new PollinationsImageProvider)->generate('a red panda');
    }
}
