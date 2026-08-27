<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentPrompt extends Model
{
    protected $fillable = [
        'user_id',
        'prompt_template_id',
        'input_content',
        'analyzed_structure',
        'final_prompt',
        'status',
        'error_message',
    ];

    protected $casts = [
        'analyzed_structure' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(
            PromptTemplate::class,
            'prompt_template_id'
        );
    }
}
