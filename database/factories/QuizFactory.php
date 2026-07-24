<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'section_id' => null,
            'title' => rtrim($this->faker->sentence(3), '.'),
            'description' => $this->faker->sentence(10),
            'order' => $this->faker->numberBetween(0, 20),
            'pass_mark' => 70,
            'time_limit_minutes' => $this->faker->randomElement([null, 10, 20, 30]),
            'max_attempts' => $this->faker->randomElement([null, 1, 3]),
        ];
    }
}
