<?php

namespace App\Http\Controllers;

use App\Http\Requests\PromptAnalyzerRequest;
use App\Jobs\AnalyzeContentJob;
use App\Models\ContentPrompt;
use App\Models\PromptTemplate;
use App\Repositories\ContentPromptRepository;
use Inertia\Inertia;

class PromptAnalyzerController extends Controller
{
    public function __construct(
        private ContentPromptRepository $contentPromptRepo
    ) {}

    public function index()
    {
        return Inertia::render('PromptAnalyzer/Index', [
            'templates' => PromptTemplate::where('is_active', true)
                ->get(['id', 'name', 'category']),
            'recentPrompts' => $this->contentPromptRepo->findByUser(
                auth()->id(),
                10
            ),
        ]);
    }

    public function store(PromptAnalyzerRequest $request)
    {
        $validated = $request->validated();

        $contentPrompt = $this->contentPromptRepo->create([
            'user_id' => auth()->id(),
            'prompt_template_id' => $validated['prompt_template_id'] ?? null,
            'input_content' => $validated['input_content'],
            'status' => ContentPrompt::STATUS_PENDING,
        ]);

        AnalyzeContentJob::dispatch($contentPrompt);

        return back()->with('prompt_id', $contentPrompt->id);
    }

    public function status(ContentPrompt $contentPrompt)
    {
        abort_if(
            $contentPrompt->user_id !== auth()->id(),
            403
        );

        return response()->json([
            'id' => $contentPrompt->id,
            'status' => $contentPrompt->status,
            'analyzed_structure' => $contentPrompt->analyzed_structure,
            'final_prompt' => $contentPrompt->final_prompt,
            'error_message' => $contentPrompt->error_message,
        ]);
    }
}
