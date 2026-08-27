<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\AnalyzeContentJob;
use App\Models\ContentPrompt;
use App\Models\PromptTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class PromptAnalyzerController extends Controller
{
    // Render Màn hình chính
    public function index()
    {
        return Inertia::render('PromptAnalyzer/Index', [
            'templates' => PromptTemplate::where('is_active', true)->get(['id', 'name', 'category']),
            'recentPrompts' => ContentPrompt::where('user_id', auth()->id())
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }

    // Submit Nội dung phân tích
    public function store(Request $request)
    {
        // $fileContent = 'demo-aws';
        // // Tải file lên S3 ảo của LocalStack
        // Storage::disk('s3')->put('avatars/user1.jpg', $fileContent);

        // // Lấy URL file
        // $url = Storage::disk('s3')->url('avatars/user1.jpg');
        // var_dump($url); die();

        // Kết quả sẽ là: http://localhost:4566/my-local-bucket/avatars/user1.jpg
        $validated = $request->validate([
            'input_content' => 'required|string|min:10',
            'prompt_template_id' => 'nullable|exists:prompt_templates,id',
        ]);

        $contentPrompt = ContentPrompt::create([
            'user_id' => auth()->id(),
            'prompt_template_id' => $validated['prompt_template_id'] ?? null,
            'input_content' => $validated['input_content'],
            'status' => 'pending',
        ]);

        // dd([
        //     'content_prompt_id' => $contentPrompt->id,
        //     'status' => $contentPrompt->status,
        // ]);

        // Đẩy vào Queue
        AnalyzeContentJob::dispatch($contentPrompt);

        return back()->with('prompt_id', $contentPrompt->id);
    }

    // API Polling nhẹ lấy trạng thái
    public function status(ContentPrompt $contentPrompt)
    {
        // Phân quyền nhẹ
        abort_if($contentPrompt->user_id !== auth()->id(), 403);

        return response()->json([
            'status' => $contentPrompt->status,
            'analyzed_structure' => $contentPrompt->analyzed_structure,
            'final_prompt' => $contentPrompt->final_prompt,
            'error_message' => $contentPrompt->error_message,
        ]);
    }
}