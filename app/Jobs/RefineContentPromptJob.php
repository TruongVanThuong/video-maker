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

class RefineContentPromptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 120;

    public function __construct(
        public ContentPrompt $contentPrompt,
        public string $instruction,
        public array $fields = ['all'],
    ) {}

    public function handle(
        PromptAnalyzerService $service,
        ContentPromptRepository $repo
    ): void {
        try {
            $result = $service->refine($this->contentPrompt, $this->instruction, $this->fields);

            $repo->markAsRefined(
                $this->contentPrompt->id,
                $result,
                $this->contentPrompt->refinement_count + 1
            );
        } catch (Throwable $e) {
            report($e);

            $repo->markAsFailed(
                $this->contentPrompt->id,
                'Không thể tinh chỉnh prompt: ' . $e->getMessage()
            );
        }
    }
}