<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\PollRenderStatusJob;
use App\Jobs\StartProjectRenderJob;
use App\Models\Project;
use App\Models\Scene;
use App\Models\User;
use App\Services\CreatomateRenderService;
use App\Services\SceneAssetGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class StartProjectRenderJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_ensures_assets_and_marks_project_rendering(): void
    {
        $project = $this->makeProject();
        $this->makeScene($project);

        $assetGenerator = Mockery::mock(SceneAssetGenerator::class);
        $assetGenerator->shouldReceive('ensureVoice')->once();
        $assetGenerator->shouldReceive('ensureImage')->once();

        $renderService = Mockery::mock(CreatomateRenderService::class);
        $renderService->shouldReceive('renderProjectVideo')
            ->with(Mockery::type(Project::class))
            ->once()
            ->andReturn('creatomate-job-123');
        $renderService->shouldReceive('expectsWebhooks')->once()->andReturn(true);

        (new StartProjectRenderJob($project))->handle($assetGenerator, $renderService);

        $fresh = $project->fresh();

        $this->assertSame(Project::STATUS_RENDERING, $fresh->status);
        $this->assertSame('creatomate-job-123', $fresh->render_job_id);
        $this->assertNull($fresh->render_error);
    }

    public function test_handle_dispatches_poll_job_when_webhooks_are_not_configured(): void
    {
        Queue::fake();

        $project = $this->makeProject();
        $this->makeScene($project);

        $assetGenerator = Mockery::mock(SceneAssetGenerator::class);
        $assetGenerator->shouldReceive('ensureVoice');
        $assetGenerator->shouldReceive('ensureImage');

        $renderService = Mockery::mock(CreatomateRenderService::class);
        $renderService->shouldReceive('renderProjectVideo')->once()->andReturn('mock-render-job');
        $renderService->shouldReceive('expectsWebhooks')->once()->andReturn(false);

        (new StartProjectRenderJob($project))->handle($assetGenerator, $renderService);

        Queue::assertPushed(PollRenderStatusJob::class);
        $this->assertSame(Project::STATUS_RENDERING, $project->fresh()->status);
    }

    public function test_failed_sets_project_failed_and_records_error(): void
    {
        $project = $this->makeProject();

        (new StartProjectRenderJob($project))->failed(new RuntimeException('boom'));

        $fresh = $project->fresh();

        $this->assertSame(Project::STATUS_FAILED, $fresh->status);
        $this->assertSame('boom', $fresh->render_error);
    }

    private function makeProject(): Project
    {
        return Project::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Test Project',
            'source_type' => 'story',
            'raw_input' => 'input',
            'status' => Project::STATUS_DRAFT,
        ]);
    }

    private function makeScene(Project $project): Scene
    {
        return Scene::create([
            'project_id' => $project->id,
            'scene_index' => 1,
            'dialogue' => 'Cảnh kiểm tra asset gen.',
            'voice_id' => 'Kore',
            'duration' => 0,
        ]);
    }
}
