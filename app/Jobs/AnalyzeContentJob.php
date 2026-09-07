<?php

namespace App\Jobs;

use App\Models\ContentPrompt;
use App\Repositories\ContentPromptRepository;
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
        PromptAnalyzerService $analyzerService,
        ContentPromptRepository $contentPromptRepo
    ): void {
        $contentPromptRepo->markAsProcessing($this->contentPrompt->id);

        try {
            $result = $analyzerService->analyze($this->contentPrompt);

            $contentPromptRepo->markAsCompleted(
                $this->contentPrompt->id,
                $result
            );
        } catch (Throwable $e) {
            $contentPromptRepo->markAsFailed(
                $this->contentPrompt->id,
                $e->getMessage()
            );

            throw $e;
        }
    }
}
