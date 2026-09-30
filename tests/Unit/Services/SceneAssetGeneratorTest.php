<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Project;
use App\Models\Scene;
use App\Models\User;
use App\Services\SceneAssetGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class SceneAssetGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config()->set('services.gemini.key', null);
        config()->set('services.falai.enabled', false);
        config()->set('services.falai.key', null);
    }

    public function test_ensure_voice_generates_audio_and_stores_relative_path(): void
    {
        config()->set('services.ai.demo_mode', true);

        $scene = $this->makeScene('Xin chào thế giới, đây là cảnh mở đầu của câu chuyện.');

        (new SceneAssetGenerator)->ensureVoice($scene);

        $fresh = $scene->fresh();

        $this->assertStringStartsWith('audio/', $fresh->audio_path);
        $this->assertGreaterThan(0.0, $fresh->duration);
        $this->assertNotNull($fresh->audio_url);
        $this->assertStringContainsString('/storage/audio/', (string) $fresh->audio_url);

        $this->assertDatabaseHas('ai_jobs', [
            'project_id' => $scene->project_id,
            'scene_id' => $scene->id,
            'job_type' => 'generate_voice',
            'status' => 'success',
        ]);
    }

    public function test_ensure_voice_skips_when_audio_already_exists(): void
    {
        $scene = $this->makeScene('Lời thoại không cần tạo lại.');
        $scene->update(['audio_path' => 'audio/existing.wav']);

        (new SceneAssetGenerator)->ensureVoice($scene);

        $this->assertSame('audio/existing.wav', $scene->fresh()->audio_path);
        $this->assertDatabaseCount('ai_jobs', 0);
    }

    public function test_ensure_image_marks_failure_and_rethrows_when_prompt_is_missing(): void
    {
        config()->set('services.ai.demo_mode', false);

        $scene = $this->makeScene('');

        $this->expectException(RuntimeException::class);

        (new SceneAssetGenerator)->ensureImage($scene);

        $this->assertNull($scene->fresh()->image_path);
        $this->assertDatabaseHas('ai_jobs', [
            'scene_id' => $scene->id,
            'job_type' => 'generate_image',
            'status' => 'failed',
        ]);
    }

    public function test_ensure_image_skips_when_image_already_exists(): void
    {
        $scene = $this->makeScene('Cảnh có sẵn ảnh rồi.');
        $scene->update(['image_path' => 'images/existing.png']);

        (new SceneAssetGenerator)->ensureImage($scene);

        $this->assertSame('images/existing.png', $scene->fresh()->image_path);
        $this->assertDatabaseCount('ai_jobs', 0);
    }

    private function makeScene(string $dialogue): Scene
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Test Project',
            'source_type' => 'story',
            'raw_input' => 'input',
            'status' => Project::STATUS_DRAFT,
        ]);

        return Scene::create([
            'project_id' => $project->id,
            'scene_index' => 1,
            'dialogue' => $dialogue,
            'voice_id' => 'Kore',
            'image_prompt' => $dialogue === '' ? null : 'Hoạt hình cổ trang.',
            'duration' => 0,
        ]);
    }
}
