<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSceneRequest extends FormRequest
{
    public function authorize(): bool
    {
        $scene = $this->route('scene');
        return $scene && $scene->project->user_id === auth()->id();
    }

    public function rules(): array
    {
        return [
            'dialogue' => 'required|string|max:5000',
            'voice_id' => 'nullable|string|max:100',
            'image_prompt' => 'nullable|string|max:5000',
            'camera_rule' => 'nullable|array',
        ];
    }
}
