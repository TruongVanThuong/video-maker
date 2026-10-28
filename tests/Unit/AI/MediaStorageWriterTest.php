<?php

declare(strict_types=1);

namespace Tests\Unit\AI;

use App\Services\AI\Support\MediaStorageWriter;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class MediaStorageWriterTest extends TestCase
{
    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    public function test_it_rejects_empty_content(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('empty');

        MediaStorageWriter::store('public', 'images/x.png', '', ['image/png']);
    }

    public function test_it_rejects_unexpected_mime_type(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('image/jpeg');

        MediaStorageWriter::store(
            'public',
            'images/x.png',
            base64_decode(self::PNG_1X1),
            ['image/jpeg'],
        );
    }

    public function test_it_stores_valid_content_with_public_visibility(): void
    {
        Storage::fake('public');

        $binary = base64_decode(self::PNG_1X1);

        MediaStorageWriter::store('public', 'images/valid.png', $binary, ['image/png']);

        Storage::disk('public')->assertExists('images/valid.png');
        $this->assertSame('public', Storage::disk('public')->getVisibility('images/valid.png'));
    }
}
