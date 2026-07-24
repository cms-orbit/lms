<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\CourseSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseSection>
 */
class CourseSectionFactory extends Factory
{
    protected $model = CourseSection::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => rtrim($this->faker->sentence(3), '.'),
            'summary' => $this->faker->sentence(8),
            'order' => $this->faker->numberBetween(0, 20),
        ];
    }
}
