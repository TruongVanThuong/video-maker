<?php

namespace Database\Factories;

use App\Models\ContentPrompt;
use App\Models\PromptTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentPrompt>
 */
class ContentPromptFactory extends Factory
{
    protected $model = ContentPrompt::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'prompt_template_id' => PromptTemplate::factory(),
            'input_content' => $this->faker->text(200),
            'analyzed_structure' => null,
            'final_prompt' => null,
            'status' => ContentPrompt::STATUS_PENDING,
            'conversion_status' => 'unconverted',
            'project_id' => null,
            'error_message' => null,
            'refinement_count' => 0,
            'is_manually_edited' => false,
        ];
    }
}
