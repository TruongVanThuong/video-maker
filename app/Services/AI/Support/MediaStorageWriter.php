<?php

declare(strict_types=1);

namespace App\Services\AI\Support;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class MediaStorageWriter
{
    /**
     * @param  string[]  $allowedMimeTypes  example ['image/png', 'image/jpeg']
     */
    public static function store(
        string $disk,
        string $path,
        string $binaryContent,
        array $allowedMimeTypes,
        string $visibility = 'public',
    ): void {
        if ($binaryContent === '') {
            throw new RuntimeException("Downloaded content is empty for path [{$path}].");
        }

        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->buffer($binaryContent);

        if (! in_array($detectedMime, $allowedMimeTypes, true)) {
            throw new RuntimeException(
                "Unexpected content-type [{$detectedMime}] for [{$path}]. Expected one of: "
                .implode(', ', $allowedMimeTypes)
            );
        }

        Storage::disk($disk)->put($path, $binaryContent, $visibility);
    }
}
