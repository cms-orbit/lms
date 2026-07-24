<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Models\Assignment;
use CmsOrbit\Lms\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'section_id' => null,
            'title' => rtrim($this->faker->sentence(4), '.'),
            'instructions' => $this->faker->paragraphs(2, true),
            'max_points' => 100,
            'pass_points' => 50,
            'due_at' => null,
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }
}
