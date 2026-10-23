<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContentPromptRequest;
use App\Jobs\AnalyzeContentJob;
use App\Models\ContentPrompt;
use App\Models\PromptTemplate;
use App\Repositories\ContentPromptRepository;
use Inertia\Inertia;
use App\Http\Requests\UpdateContentPromptRequest;
use App\Http\Requests\RefineContentPromptRequest;
use App\Jobs\RefineContentPromptJob;

class PromptAnalyzerController extends Controller
{
    private const HISTORY_PER_PAGE = 10;

    public function __construct(
        private ContentPromptRepository $contentPromptRepo
    ) {}

    public function index()
    {
        return Inertia::render('PromptAnalyzer/Index', [
            'templates' => PromptTemplate::where('is_active', true)
                ->get(['id', 'name', 'category']),
            'recentPrompts' => ContentPrompt::query()
                ->where('user_id', auth()->id())
                ->latest()
                ->orderByDesc('id')
                ->paginate(self::HISTORY_PER_PAGE)
                ->withQueryString()
                ->through(fn ($prompt) => [
                    'id' => $prompt->id,
                    'input_content' => $prompt->input_content,
                    'status' => $prompt->status,
                    'conversion_status' => $prompt->conversion_status,
                    'project_id' => $prompt->project_id,
                    'analyzed_structure' => $prompt->analyzed_structure,
                    'final_prompt' => $prompt->final_prompt,
                    'error_message' => $prompt->error_message,
                    'target_platform' => $prompt->target_platform,
                    'prompt_template_id' => $prompt->prompt_template_id,
                    'refinement_count' => $prompt->refinement_count,
                    'is_manually_edited' => $prompt->is_manually_edited,
                    'created_at' => $prompt->created_at,
                ]),
        ]);
    }

    public function store(ContentPromptRequest $request)
    {
        $validated = $request->validated();

        $contentPrompt = $this->contentPromptRepo->create([
            'user_id' => auth()->id(),
            'prompt_template_id' => $validated['prompt_template_id'] ?? null,
            'target_platform' => $validated['target_platform'] ?? null,
            'input_content' => $validated['input_content'],
            'status' => ContentPrompt::STATUS_PENDING,
        ]);

        AnalyzeContentJob::dispatch($contentPrompt);

        return back()->with('prompt_id', $contentPrompt->id);
    }

    public function status(ContentPrompt $contentPrompt)
    {
        $this->authorizeOwner($contentPrompt);

        return response()->json([
            'id' => $contentPrompt->id,
            'status' => $contentPrompt->status,
            'conversion_status' => $contentPrompt->conversion_status,
            'project_id' => $contentPrompt->project_id,
            'input_content' => $contentPrompt->input_content,
            'prompt_template_id' => $contentPrompt->prompt_template_id,
            'target_platform' => $contentPrompt->target_platform,
            'analyzed_structure' => $contentPrompt->analyzed_structure,
            'final_prompt' => $contentPrompt->final_prompt,
            'error_message' => $contentPrompt->error_message,
            'refinement_count' => $contentPrompt->refinement_count,
            'is_manually_edited' => $contentPrompt->is_manually_edited,
        ]);
    }

    public function update(UpdateContentPromptRequest $request, ContentPrompt $contentPrompt)
    {
        $this->authorizeOwner($contentPrompt);

        abort_unless(
            $contentPrompt->isCompleted(),
            422,
            'Chỉ có thể chỉnh sửa khi prompt đã hoàn thành.'
        );

        $validated = $request->validated();

        $this->contentPromptRepo->applyManualEdit(
            $contentPrompt->id,
            $validated['analyzed_structure'] ?? [],
            $validated['final_prompt'] ?? null
        );

        return response()->json(
            $this->contentPromptRepo->findOrFail($contentPrompt->id)
        );
    }

    public function retry(ContentPrompt $contentPrompt)
    {
        $this->authorizeOwner($contentPrompt);

        abort_unless(
            $contentPrompt->isFailed(),
            422,
            'Chỉ có thể thử lại khi prompt đã thất bại.'
        );

        $this->contentPromptRepo->markAsRetrying($contentPrompt->id);

        AnalyzeContentJob::dispatch($contentPrompt);

        return response()->json([
            'id' => $contentPrompt->id,
            'status' => ContentPrompt::STATUS_PROCESSING,
        ]);
    }

    public function refine(RefineContentPromptRequest $request, ContentPrompt $contentPrompt)
    {
        $this->authorizeOwner($contentPrompt);

        abort_unless(
            $contentPrompt->canBeRefined(),
            422,
            $contentPrompt->refinement_count >= ContentPrompt::MAX_REFINEMENTS
                ? 'Đã đạt giới hạn số lần tinh chỉnh cho prompt này.'
                : 'Chỉ có thể tinh chỉnh khi prompt đã hoàn thành.'
        );

        $validated = $request->validated();

        $this->contentPromptRepo->markAsProcessing($contentPrompt->id);

        RefineContentPromptJob::dispatch(
            $contentPrompt,
            $validated['instruction'],
            $validated['fields'] ?? ['all']
        );

        return response()->json(['status' => 'processing']);
    }

    public function convertToProject(ContentPrompt $contentPrompt, \App\Actions\ConvertContentPromptToProjectAction $action)
    {
        $this->authorizeOwner($contentPrompt);

        if ($contentPrompt->conversion_status === 'converted' && $contentPrompt->project_id) {
            return redirect()->route('projects.studio', $contentPrompt->project_id);
        }

        $project = $action->execute($contentPrompt);

        return redirect()->route('projects.studio', $project->id);
    }

    private function authorizeOwner(ContentPrompt $contentPrompt): void
    {
        abort_if($contentPrompt->user_id !== auth()->id(), 403);
    }
}
