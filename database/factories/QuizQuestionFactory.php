<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Enums\QuestionType;
use CmsOrbit\Lms\Models\Quiz;
use CmsOrbit\Lms\Models\QuizQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizQuestion>
 */
class QuizQuestionFactory extends Factory
{
    protected $model = QuizQuestion::class;

    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'question' => rtrim($this->faker->sentence(8), '.').'?',
            'type' => QuestionType::Single->value,
            'options' => ['A', 'B', 'C', 'D'],
            'correct' => [0],
            'points' => 1,
            'order' => $this->faker->numberBetween(0, 20),
            'explanation' => null,
        ];
    }

    public function trueFalse(): static
    {
        return $this->state(fn (): array => [
            'type' => QuestionType::TrueFalse->value,
            'options' => [__('True'), __('False')],
            'correct' => [0],
        ]);
    }
}
