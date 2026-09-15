<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefineContentPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'instruction' => ['required', 'string', 'min:5', 'max:1000'],
            'fields' => ['sometimes', 'array', 'min:1'],
            'fields.*' => [
                'string',
                Rule::in([
                    'all',
                    'subject',
                    'action',
                    'environment',
                    'camera_movement',
                    'lighting_and_atmosphere',
                    'final_video_prompt',
                ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'instruction.required' => 'Vui lòng nhập yêu cầu chỉnh sửa cụ thể.',
            'instruction.min' => 'Yêu cầu chỉnh sửa quá ngắn, hãy mô tả rõ hơn.',
        ];
    }
}