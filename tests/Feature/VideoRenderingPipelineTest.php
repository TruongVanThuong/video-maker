<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\VideoReady;
use App\Jobs\BuildVideoTimelineJob;
use App\Models\AiJob;
use App\Models\Project;
use App\Models\Scene;
use App\Models\User;
use App\Models\ProjectMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoRenderingPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Overwrite configurations for tests
        config([
            'video.provider' => 'creatomate',
            'video.creatomate.api_key' => 'test-api-key',
            'video.creatomate.endpoint' => 'https://api.creatomate.com/v2',
            'video.webhook_url' => 'http://localhost/api/webhooks/video-rendered',
            'video.webhook_token' => 'test-token',
        ]);
    }

    public function test_render_trigger_endpoint_dispatches_job(): void
    {
        Queue::fake();

        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('password');
        $user->save();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Triggerable Video Project',
            'status' => 'draft',
            'source_type' => 'story',
            'raw_input' => 'Some raw input text',
        ]);

        Scene::create([
            'project_id' => $project->id,
            'scene_index' => 1,
            'dialogue' => 'Has scenes',
            'duration' => 5.0,
        ]);

        // Call POST endpoint
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/render");

        $response->assertStatus(202);
        $response->assertJson([
            'message' => 'Rendering pipeline triggered successfully.',
            'status' => 'processing',
        ]);

        Queue::assertPushed(BuildVideoTimelineJob::class, function ($job) use ($project) {
            // Check that job has correct project ID using Reflection
            $reflection = new \ReflectionClass($job);
            $property = $reflection->getProperty('projectId');
            $property->setAccessible(true);
            return $property->getValue($job) === $project->id;
        });
    }

    public function test_render_trigger_endpoint_validation_no_scenes(): void
    {
        Queue::fake();

        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('password');
        $user->save();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'No Scenes Project',
            'status' => 'draft',
            'source_type' => 'story',
            'raw_input' => 'No scenes',
        ]);

        // Call POST endpoint
        $response = $this->actingAs($user)->postJson("/api/projects/{$project->id}/render");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Project has no scenes to render.',
        ]);

        Queue::assertNotPushed(BuildVideoTimelineJob::class);
    }

    public function test_timeline_is_built_and_rendered_successfully_via_job(): void
    {
        Http::fake([
            'https://api.creatomate.com/v2/renders' => Http::response([
                'id' => 'mock-render-job-uuid-123',
                'status' => 'rendering',
            ], 200),
        ]);

        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('password');
        $user->save();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Test Video Title',
            'status' => 'draft',
            'source_type' => 'story',
            'raw_input' => 'Some raw input text',
        ]);

        Scene::create([
            'project_id' => $project->id,
            'scene_index' => 1,
            'dialogue' => 'Hello this is scene 1',
            'audio_path' => 'https://example.com/audio1.mp3',
            'image_path' => 'https://example.com/image1.jpg',
            'duration' => 5.50,
            'camera_rule' => ['type' => 'zoom_in', 'speed' => 'slow'],
        ]);

        // Dispatch job synchronously
        BuildVideoTimelineJob::dispatchSync($project->id);

        // Refresh and check database
        $project->refresh();
        $this->assertEquals('rendering', $project->status);
        $this->assertEquals('mock-render-job-uuid-123', $project->render_job_id);

        // Assert AiJob was logged and updated
        $aiJob = AiJob::where('project_id', $project->id)->first();
        $this->assertNotNull($aiJob);
        $this->assertEquals('render_video', $aiJob->job_type);
        $this->assertEquals('processing', $aiJob->status); 
        $this->assertEquals('mock-render-job-uuid-123', $aiJob->response_data['id']);

        Http::assertSent(function ($request) {
            $payload = json_decode($request->body(), true);
            return $payload['output_format'] === 'mp4'
                && count($payload['elements']) === 3 // Image, Audio, Subtitle text
                && $payload['elements'][0]['type'] === 'image'
                && $payload['elements'][1]['type'] === 'audio'
                && $payload['elements'][2]['type'] === 'text';
        });
    }

    public function test_webhook_successful_callback_saves_media_and_completes_project(): void
    {
        Storage::fake('public');
        Event::fake([VideoReady::class]);

        // Fake HEAD request for content length and GET request for downloading file
        Http::fake([
            'https://example.com/final_video.mp4' => function ($request) {
                if ($request->method() === 'HEAD') {
                    return Http::response('', 200, ['Content-Length' => '102400']);
                }
                return Http::response('fake video binary data', 200);
            },
        ]);

        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('password');
        $user->save();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Rendering Video',
            'status' => 'rendering',
            'render_job_id' => 'mock-render-job-uuid-123',
            'source_type' => 'story',
            'raw_input' => 'Some raw input text',
        ]);

        Scene::create([
            'project_id' => $project->id,
            'scene_index' => 1,
            'dialogue' => 'Scene 1',
            'duration' => 6.0,
        ]);

        // Create initial AiJob
        $aiJob = AiJob::create([
            'project_id' => $project->id,
            'job_type' => 'render_video',
            'provider' => 'creatomate',
            'status' => 'processing',
        ]);

        $payload = [
            'id' => 'mock-render-job-uuid-123',
            'status' => 'succeeded',
            'url' => 'https://example.com/final_video.mp4',
        ];

        // Perform POST request to webhook url
        $response = $this->postJson('/api/webhooks/video-rendered?token=test-token', $payload);

        $response->assertStatus(200);

        // Assert project status updated
        $project->refresh();
        $this->assertEquals('completed', $project->status);

        // Assert media saved with local storage file url and size
        $media = ProjectMedia::where('project_id', $project->id)->first();
        $this->assertNotNull($media);
        $this->assertEquals('final_video', $media->media_type);
        $this->assertStringContainsString('/storage/renders/project_', $media->file_url);
        $this->assertStringContainsString('renders/project_', $media->file_path);
        $this->assertEquals(6.0, $media->total_duration);
        $this->assertEquals(102400, $media->file_size);

        // Assert file actually downloaded and stored
        Storage::disk('public')->assertExists($media->file_path);
        $this->assertEquals('fake video binary data', Storage::disk('public')->get($media->file_path));

        // Assert AiJob updated to success
        $aiJob->refresh();
        $this->assertEquals('success', $aiJob->status);
        $this->assertEquals('succeeded', $aiJob->response_data['status']);

        // Assert VideoReady event dispatched
        Event::assertDispatched(VideoReady::class, function ($event) use ($project) {
            return $event->project->id === $project->id;
        });
    }

    public function test_webhook_failed_callback_updates_project_to_failed(): void
    {
        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('password');
        $user->save();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Rendering Video',
            'status' => 'rendering',
            'render_job_id' => 'mock-render-job-uuid-123',
            'source_type' => 'story',
            'raw_input' => 'Some raw input text',
        ]);

        // Create initial AiJob
        $aiJob = AiJob::create([
            'project_id' => $project->id,
            'job_type' => 'render_video',
            'provider' => 'creatomate',
            'status' => 'processing',
        ]);

        $payload = [
            'id' => 'mock-render-job-uuid-123',
            'status' => 'failed',
            'error_message' => 'Something went wrong on Creatomate.',
        ];

        $response = $this->postJson('/api/webhooks/video-rendered?token=test-token', $payload);

        $response->assertStatus(200);

        $project->refresh();
        $this->assertEquals('failed', $project->status);

        // Assert AiJob marked failed
        $aiJob->refresh();
        $this->assertEquals('failed', $aiJob->status);
        $this->assertEquals('Something went wrong on Creatomate.', $aiJob->error_message);
    }

    public function test_webhook_unauthorized_token(): void
    {
        $payload = [
            'id' => 'mock-render-job-uuid-123',
            'status' => 'succeeded',
            'url' => 'https://example.com/final_video.mp4',
        ];

        $response = $this->postJson('/api/webhooks/video-rendered?token=wrong-token', $payload);
        $response->assertStatus(401);
    }

    public function test_webhook_idempotency(): void
    {
        $user = new User();
        $user->name = 'Test User';
        $user->email = 'test@example.com';
        $user->password = bcrypt('password');
        $user->save();

        // Completed project
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Finished Video',
            'status' => 'completed',
            'render_job_id' => 'mock-render-job-uuid-123',
            'source_type' => 'story',
            'raw_input' => 'Some raw input text',
        ]);

        $payload = [
            'id' => 'mock-render-job-uuid-123',
            'status' => 'succeeded',
            'url' => 'https://example.com/final_video.mp4',
        ];

        $response = $this->postJson('/api/webhooks/video-rendered?token=test-token', $payload);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Already processed.']);
    }
}
