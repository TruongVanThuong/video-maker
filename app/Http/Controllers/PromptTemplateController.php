<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePromptTemplateRequest;
use App\Models\PromptTemplate;
use App\Repositories\PromptTemplateRepository;
use App\Services\GeminiApiClient;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Throwable;

class PromptTemplateController extends Controller
{
    public function __construct(
        private PromptTemplateRepository $promptTemplateRepository
    ) {}

    public function index(Request $request)
    {
        $category = $request->input('category');
        $search = $request->input('search');

        $templates = $this->promptTemplateRepository->getAll($category, $search, false, 12);
        $categories = $this->promptTemplateRepository->getCategories();

        return Inertia::render('PromptTemplate/Index', [
            'templates' => $templates,
            'categories' => $categories,
            'filters' => [
                'category' => $category,
                'search' => $search,
            ],
        ]);
    }

    public function store(SavePromptTemplateRequest $request)
    {
        $validated = $request->validated();
        $this->promptTemplateRepository->create($validated);

        return back()->with('success', 'Prompt Template created successfully.');
    }

    public function update(SavePromptTemplateRequest $request, PromptTemplate $promptTemplate)
    {
        $validated = $request->validated();
        $this->promptTemplateRepository->update($promptTemplate->id, $validated);

        return back()->with('success', 'Prompt Template updated successfully.');
    }

    public function duplicate(PromptTemplate $promptTemplate)
    {
        $this->promptTemplateRepository->duplicate($promptTemplate->id);

        return back()->with('success', 'Prompt Template duplicated successfully.');
    }

    public function toggleStatus(PromptTemplate $promptTemplate)
    {
        $this->promptTemplateRepository->toggleStatus($promptTemplate->id);

        return back()->with('success', 'Template status updated.');
    }

    public function destroy(PromptTemplate $promptTemplate)
    {
        $this->promptTemplateRepository->delete($promptTemplate->id);

        return back()->with('success', 'Prompt Template deleted successfully.');
    }

    public function test(Request $request, GeminiApiClient $geminiClient)
    {
        $request->validate([
            'system_instruction' => 'required|string',
            'sample_input' => 'required|string|min:5|max:5000',
        ]);

        try {
            $systemInstruction = $request->input('system_instruction');
            $userPrompt = "Analyze the following sample content into a video prompt:\n\n" . $request->input('sample_input');
            
            $jsonSchema = [
                'type' => 'object',
                'properties' => [
                    'subject' => ['type' => 'string', 'description' => 'Main subject'],
                    'action' => ['type' => 'string', 'description' => 'Action performed'],
                    'environment' => ['type' => 'string', 'description' => 'Environment'],
                    'camera_movement' => ['type' => 'string', 'description' => 'Camera movement'],
                    'lighting_and_atmosphere' => ['type' => 'string', 'description' => 'Lighting'],
                    'final_video_prompt' => ['type' => 'string', 'description' => 'Final video prompt in English'],
                ],
                'required' => ['subject', 'action', 'environment', 'camera_movement', 'lighting_and_atmosphere', 'final_video_prompt'],
            ];

            $result = $geminiClient->generateContent($systemInstruction, $userPrompt, $jsonSchema);

            return response()->json([
                'success' => true,
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
