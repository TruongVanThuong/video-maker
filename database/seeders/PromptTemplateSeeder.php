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
            'name' => 'Cinematic Realistic Video',
            'category' => 'cinematic',
            'system_instruction' => 'Analyze input text and convert into a highly detailed cinematic video prompt. Focus on camera movement, lighting, subject action, atmosphere, and high resolution details for AI Video Generator.',
        ]);
    }
}