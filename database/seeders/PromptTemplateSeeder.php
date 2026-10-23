<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PromptTemplate;

class PromptTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // database/seeders/PromptTemplateSeeder.php
        PromptTemplate::create([
            'name' => 'Video điện ảnh chân thực',
            'category' => 'cinematic',
            'system_instruction' => 'Phân tích văn bản đầu vào và chuyển thành một prompt video điện ảnh chi tiết cao. Tập trung vào chuyển động camera, ánh sáng, hành động của chủ thể, không khí và chi tiết độ phân giải cao cho AI Video Generator.',
        ]);
    }
}