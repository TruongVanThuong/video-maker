<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\PollRenderStatusJob;
use App\Models\Project;
use App\Models\User;
use App\Services\CreatomateRenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PollRenderStatusJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_completes_project_when_render_is_finished(): void
    {
        config()->set('services.creatomate.key', '');

        $project = $this->makeRenderingProject();

        (new PollRenderStatusJob($project))->handle(app(CreatomateRenderService::class));

        $fresh = $project->fresh();

        $this->assertSame(Project::STATUS_COMPLETED, $fresh->status);
        $this->assertNotNull($fresh->video_path);
        $this->assertNotNull($fresh->rendered_at);
        $this->assertNull($fresh->render_error);
    }

    public function test_handle_marks_project_failed_when_render_reports_error(): void
    {
        $project = $this->makeRenderingProject();

        $service = Mockery::mock(CreatomateRenderService::class);
        $service->shouldReceive('getRenderStatus')
            ->once()
            ->andReturn(['status' => 'failed', 'url' => null, 'error' => 'engine exploded']);

        (new PollRenderStatusJob($project))->handle($service);

        $fresh = $project->fresh();

        $this->assertSame(Project::STATUS_FAILED, $fresh->status);
        $this->assertSame('engine exploded', $fresh->render_error);
    }

    public function test_handle_skips_polling_when_project_is_already_finished(): void
    {
        $project = $this->makeRenderingProject();
        $project->update(['status' => Project::STATUS_COMPLETED]);

        $service = Mockery::mock(CreatomateRenderService::class);
        $service->shouldReceive('getRenderStatus')->never();

        (new PollRenderStatusJob($project))->handle($service);

        $this->assertSame(Project::STATUS_COMPLETED, $project->fresh()->status);
    }

    public function test_handle_marks_failed_when_render_job_id_is_missing(): void
    {
        $project = $this->makeRenderingProject();
        $project->update(['render_job_id' => null]);

        $service = Mockery::mock(CreatomateRenderService::class);
        $service->shouldReceive('getRenderStatus')->never();

        $this->expectExceptionMessage('no render_job_id');

        (new PollRenderStatusJob($project))->handle($service);
    }

    private function makeRenderingProject(): Project
    {
        return Project::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Test Project',
            'source_type' => 'story',
            'raw_input' => 'input',
            'status' => Project::STATUS_RENDERING,
            'render_job_id' => 'mock-render-job',
        ]);
    }
}
