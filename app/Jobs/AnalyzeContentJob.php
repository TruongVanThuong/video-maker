<?php

namespace App\Jobs;

use App\Models\ContentPrompt;
use App\Services\PromptAnalyzerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class AnalyzeContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public ContentPrompt $contentPrompt
    ) {}

    public function handle(
        PromptAnalyzerService $analyzerService
    ): void {
        dd([
            'job_running' => true,
            'content_prompt_id' => $this->contentPrompt->id,
        ]);
        $this->contentPrompt->update([
            'status' => 'processing',
            'error_message' => null,
        ]);

        try {
            $result = $analyzerService->analyze(
                $this->contentPrompt
            );

            $this->contentPrompt->update([
                'analyzed_structure' => $result,
                'final_prompt' => $result['final_video_prompt'],
                'status' => 'completed',
            ]);
        } catch (Throwable $e) {
            $this->contentPrompt->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}