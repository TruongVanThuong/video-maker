<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContentPromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'analyzed_structure' => ['sometimes', 'array'],
            'analyzed_structure.subject' => ['sometimes', 'nullable', 'string', 'max:500'],
            'analyzed_structure.action' => ['sometimes', 'nullable', 'string', 'max:500'],
            'analyzed_structure.environment' => ['sometimes', 'nullable', 'string', 'max:500'],
            'analyzed_structure.camera_movement' => ['sometimes', 'nullable', 'string', 'max:500'],
            'analyzed_structure.lighting_and_atmosphere' => ['sometimes', 'nullable', 'string', 'max:500'],
            'final_prompt' => ['sometimes', 'nullable', 'string', 'max:4000'],
        ];
    }
}