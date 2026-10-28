<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromptTemplate extends Model
{
    protected $fillable = [
        'name',
        'category',
        'system_instruction',
        'is_active',
    ];

    public function contentPrompts()
    {
        return $this->hasMany(ContentPrompt::class, 'prompt_template_id');
    }
}