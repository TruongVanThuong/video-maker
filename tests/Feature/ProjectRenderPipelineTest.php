<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\StartProjectRenderJob;
use App\Models\Project;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProjectRenderPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_render_returns_200_and_dispatches_job_asynchronously(): void
    {
        Queue::fake();

        $project = $this->createProjectWithScene();

        $response = $this->postJson("/projects/{$project->id}/render");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('project.status', Project::STATUS_PROCESSING)
            ->assertJsonPath('project.id', $project->id);

        $this->assertSame(Project::STATUS_PROCESSING, $project->fresh()->status);

        Queue::assertPushed(StartProjectRenderJob::class, 1);
    }

    public function test_start_render_returns_409_when_status_is_processing(): void
    {
        Queue::fake();

        $project = $this->createProjectWithScene(Project::STATUS_PROCESSING);

        $this->postJson("/projects/{$project->id}/render")
            ->assertStatus(409)
            ->assertJsonPath('project.status', Project::STATUS_PROCESSING);

        Queue::assertNotPushed(StartProjectRenderJob::class);
    }

    public function test_start_render_returns_409_when_status_is_rendering(): void
    {
        Queue::fake();

        $project = $this->createProjectWithScene(Project::STATUS_RENDERING);

        $this->postJson("/projects/{$project->id}/render")
            ->assertStatus(409);

        Queue::assertNotPushed(StartProjectRenderJob::class);
    }

    public function test_start_render_returns_422_without_scenes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Empty Project',
            'source_type' => 'story',
            'raw_input' => 'input',
            'status' => Project::STATUS_DRAFT,
        ]);

        $this->postJson("/projects/{$project->id}/render")
            ->assertStatus(422);
    }

    public function test_webhook_completes_project(): void
    {
        $project = $this->createRenderingProject('creatomate-webhook-job');

        $this->postJson('/api/webhooks/creatomate', [
            'id' => 'creatomate-webhook-job',
            'status' => 'finished',
            'url' => 'https://cdn.example.com/videos/final.mp4',
        ])->assertOk();

        $fresh = $project->fresh();

        $this->assertSame(Project::STATUS_COMPLETED, $fresh->status);
        $this->assertSame('https://cdn.example.com/videos/final.mp4', $fresh->video_path);
        $this->assertNotNull($fresh->rendered_at);
        $this->assertSame('https://cdn.example.com/videos/final.mp4', $fresh->video_url);
    }

    public function test_webhook_marks_project_failed(): void
    {
        $project = $this->createRenderingProject('creatomate-webhook-job-2');

        $this->postJson('/api/webhooks/creatomate', [
            'id' => 'creatomate-webhook-job-2',
            'status' => 'failed',
            'error' => 'Creatomate ran out of credits.',
        ])->assertOk();

        $fresh = $project->fresh();

        $this->assertSame(Project::STATUS_FAILED, $fresh->status);
        $this->assertSame('Creatomate ran out of credits.', $fresh->render_error);
    }

    public function test_webhook_returns_404_for_unknown_render_job(): void
    {
        $this->postJson('/api/webhooks/creatomate', [
            'id' => 'does-not-exist',
            'status' => 'finished',
        ])->assertStatus(404);
    }

    public function test_render_status_endpoint_returns_current_state(): void
    {
        $project = $this->createRenderingProject('status-check-job');
        $this->actingAs($project->user);

        $this->getJson("/projects/{$project->id}/render-status")
            ->assertOk()
            ->assertJsonPath('project.id', $project->id)
            ->assertJsonPath('project.status', Project::STATUS_RENDERING)
            ->assertJsonPath('project.render_job_id', 'status-check-job');
    }

    public function test_render_status_endpoint_forbids_non_owner(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Private Project',
            'source_type' => 'story',
            'raw_input' => 'secret',
            'status' => Project::STATUS_DRAFT,
        ]);

        $this->actingAs($intruder)
            ->getJson("/projects/{$project->id}/render-status")
            ->assertStatus(403);
    }

    private function createProjectWithScene(string $status = Project::STATUS_DRAFT): Project
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Pipeline Test',
            'source_type' => 'story',
            'raw_input' => 'input',
            'status' => $status,
        ]);

        Scene::create([
            'project_id' => $project->id,
            'scene_index' => 1,
            'dialogue' => 'Cảnh kiểm tra pipeline.',
            'voice_id' => 'Kore',
            'duration' => 0,
        ]);

        return $project;
    }

    private function createRenderingProject(string $renderJobId): Project
    {
        return Project::create([
            'user_id' => User::factory()->create()->id,
            'title' => 'Rendering Project',
            'source_type' => 'story',
            'raw_input' => 'input',
            'status' => Project::STATUS_RENDERING,
            'render_job_id' => $renderJobId,
        ]);
    }
}
