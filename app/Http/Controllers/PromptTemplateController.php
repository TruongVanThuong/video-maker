<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePromptTemplateRequest;
use App\Models\PromptTemplate;
use App\Repositories\PromptTemplateRepository;
use App\Services\AI\LanguagePolicy;
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

        return back()->with('success', 'Tạo Prompt Template thành công.');
    }

    public function update(SavePromptTemplateRequest $request, PromptTemplate $promptTemplate)
    {
        $validated = $request->validated();
        $this->promptTemplateRepository->update($promptTemplate->id, $validated);

        return back()->with('success', 'Cập nhật Prompt Template thành công.');
    }

    public function duplicate(PromptTemplate $promptTemplate)
    {
        $this->promptTemplateRepository->duplicate($promptTemplate->id);

        return back()->with('success', 'Nhân bản Prompt Template thành công.');
    }

    public function toggleStatus(PromptTemplate $promptTemplate)
    {
        $this->promptTemplateRepository->toggleStatus($promptTemplate->id);

        return back()->with('success', 'Cập nhật trạng thái template thành công.');
    }

    public function destroy(PromptTemplate $promptTemplate)
    {
        $this->promptTemplateRepository->delete($promptTemplate->id);

        return back()->with('success', 'Xóa Prompt Template thành công.');
    }

    public function test(Request $request, GeminiApiClient $geminiClient, LanguagePolicy $languagePolicy)
    {
        $request->validate([
            'system_instruction' => 'required|string',
            'sample_input' => 'required|string|min:5|max:5000',
        ]);

        try {
            $systemInstruction = $languagePolicy->withOutputLanguageRule($request->input('system_instruction'));
            $userPrompt = "Phân tích nội dung mẫu sau thành một prompt video:\n\n" . $request->input('sample_input');

            $jsonSchema = [
                'type' => 'object',
                'properties' => [
                    'subject' => ['type' => 'string', 'description' => 'Chủ thể chính'],
                    'action' => ['type' => 'string', 'description' => 'Hành động của chủ thể'],
                    'environment' => ['type' => 'string', 'description' => 'Bối cảnh'],
                    'camera_movement' => ['type' => 'string', 'description' => 'Chuyển động camera'],
                    'lighting_and_atmosphere' => ['type' => 'string', 'description' => 'Ánh sáng và không khí'],
                    'final_video_prompt' => ['type' => 'string', 'description' => 'Prompt video hoàn chỉnh bằng ' . $languagePolicy->languageName()],
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
