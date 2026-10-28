<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'input_content' => 'required|string|min:10|max:10000',
            'prompt_template_id' => 'nullable|exists:prompt_templates,id',
            'target_platform' => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'input_content.required' => 'Nội dung không được để trống.',
            'input_content.min' => 'Nội dung phải có ít nhất 10 ký tự.',
            'input_content.max' => 'Nội dung không được vượt quá 10000 ký tự.',
            'prompt_template_id.exists' => 'Template không tồn tại.',
        ];
    }
}
