<?php
// app/Console/Commands/NormalizeSceneAssetPaths.php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Scene;
use Illuminate\Console\Command;

class NormalizeSceneAssetPaths extends Command
{
    protected $signature = 'scenes:normalize-asset-paths';
    protected $description = 'Chuyển audio_path/image_path đã lưu dạng URL tuyệt đối về path tương đối.';

    public function handle(): int
    {
        $scenes = Scene::query()
            ->where(function ($q) {
                $q->where('audio_path', 'like', 'http%')
                  ->orWhere('image_path', 'like', 'http%');
            })
            ->get();

        $this->info("Tìm thấy {$scenes->count()} scene cần normalize.");

        foreach ($scenes as $scene) {
            $updates = [];

            if (!empty($scene->audio_path) && str_starts_with($scene->audio_path, 'http')) {
                $updates['audio_path'] = $this->extractRelativePath($scene->audio_path);
            }

            if (!empty($scene->image_path) && str_starts_with($scene->image_path, 'http')) {
                $updates['image_path'] = $this->extractRelativePath($scene->image_path);
            }

            if (!empty($updates)) {
                $scene->update($updates);
                $this->line("Scene #{$scene->id} -> ".json_encode($updates));
            }
        }

        $this->info('Hoàn tất.');

        return self::SUCCESS;
    }

    private function extractRelativePath(string $absoluteUrl): string
    {
        // "http://localhost:8000/storage/images/xxx.png" -> "images/xxx.png"
        $path = parse_url($absoluteUrl, PHP_URL_PATH) ?? '';

        return ltrim(str_replace('/storage/', '', $path), '/');
    }
}