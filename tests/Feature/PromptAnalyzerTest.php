<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\AnalyzeContentJob;
use App\Models\ContentPrompt;
use App\Models\Project;
use App\Models\PromptTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PromptAnalyzerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_paginates_recent_prompts(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        PromptTemplate::factory()->create(['is_active' => true]);

        for ($i = 0; $i < 12; $i++) {
            ContentPrompt::factory()->create([
                'user_id' => $user->id,
                'input_content' => "Prompt {$i}",
                'status' => ContentPrompt::STATUS_COMPLETED,
                'created_at' => now()->subMinutes(12 - $i),
            ]);
        }

        ContentPrompt::factory()->create([
            'user_id' => $otherUser->id,
            'input_content' => 'Other user',
            'status' => ContentPrompt::STATUS_COMPLETED,
        ]);

        $response = $this->actingAs($user)->get(route('prompts.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PromptAnalyzer/Index')
            ->has('recentPrompts', function ($paginator) {
                $paginator
                    ->has('data')
                    ->has('current_page')
                    ->has('last_page')
                    ->has('per_page')
                    ->has('total')
                    ->has('links')
                    ->etc();
            })
        );

        $props = $response->original?->getData()['page'] ?? null;
        // Fallback for testing
        $pageArray = $response->inertiaPage();
        $this->assertSame(10, count($pageArray['props']['recentPrompts']['data']));
        $this->assertSame(2, $pageArray['props']['recentPrompts']['last_page']);
        $this->assertSame(12, $pageArray['props']['recentPrompts']['total']);

        $firstPrompt = $pageArray['props']['recentPrompts']['data'][0];
        $this->assertArrayHasKey('conversion_status', $firstPrompt);
        $this->assertArrayHasKey('project_id', $firstPrompt);
        $this->assertArrayHasKey('final_prompt', $firstPrompt);
        $this->assertArrayHasKey('analyzed_structure', $firstPrompt);
        $this->assertArrayHasKey('prompt_template_id', $firstPrompt);
    }

    public function test_convert_completed_prompt_redirects_to_studio_and_creates_project(): void
    {
        $user = User::factory()->create();
        $prompt = ContentPrompt::factory()->create([
            'user_id' => $user->id,
            'status' => ContentPrompt::STATUS_COMPLETED,
            'conversion_status' => 'unconverted',
            'project_id' => null,
            'final_prompt' => 'A cinematic final prompt',
            'analyzed_structure' => [
                'subject' => 'A lone astronaut',
                'action' => 'Walking across the red desert',
                'environment' => 'Mars at sunset',
                'camera_movement' => 'dolly_in',
            ],
        ]);

        $response = $this->actingAs($user)
            ->post(route('prompts.convert', $prompt));

        $project = Project::query()
            ->where('content_prompt_id', $prompt->id)
            ->firstOrFail();

        $response->assertRedirect(route('projects.studio', $project->id));

        $prompt->refresh();
        $this->assertSame('converted', $prompt->conversion_status);
        $this->assertSame($project->id, $prompt->project_id);
        $this->assertSame(1, $project->scenes()->count());
    }

    public function test_convert_already_converted_prompt_reuses_existing_project(): void
    {
        $user = User::factory()->create();
        $prompt = ContentPrompt::factory()->create([
            'user_id' => $user->id,
            'status' => ContentPrompt::STATUS_COMPLETED,
        ]);

        $project = Project::create([
            'user_id' => $user->id,
            'content_prompt_id' => $prompt->id,
            'title' => 'Existing project',
            'source_type' => 'story',
            'raw_input' => $prompt->input_content,
            'status' => Project::STATUS_DRAFT,
        ]);

        $prompt->update([
            'conversion_status' => 'converted',
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($user)
            ->post(route('prompts.convert', $prompt));

        $response->assertRedirect(route('projects.studio', $project->id));
        $this->assertDatabaseCount('projects', 1);
    }

    public function test_retry_requeues_failed_prompt_and_returns_processing(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $prompt = ContentPrompt::factory()->create([
            'user_id' => $user->id,
            'status' => ContentPrompt::STATUS_FAILED,
            'error_message' => 'old error',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('prompts.retry', $prompt));

        $response->assertOk()
            ->assertJson([
                'id' => $prompt->id,
                'status' => 'processing',
            ]);

        $prompt->refresh();
        $this->assertSame(ContentPrompt::STATUS_PROCESSING, $prompt->status);
        $this->assertNull($prompt->error_message);
        Queue::assertPushed(AnalyzeContentJob::class);
    }

    public function test_retry_forbidden_for_non_owner(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $prompt = ContentPrompt::factory()->create([
            'user_id' => $other->id,
            'status' => ContentPrompt::STATUS_FAILED,
        ]);

        $this->actingAs($user)
            ->postJson(route('prompts.retry', $prompt))
            ->assertForbidden();
    }

    public function test_retry_rejects_non_failed_prompt(): void
    {
        $user = User::factory()->create();
        $prompt = ContentPrompt::factory()->create([
            'user_id' => $user->id,
            'status' => ContentPrompt::STATUS_COMPLETED,
        ]);

        $this->actingAs($user)
            ->postJson(route('prompts.retry', $prompt))
            ->assertStatus(422);
    }
}
