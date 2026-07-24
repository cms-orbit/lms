<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Enums\CourseLevel;
use CmsOrbit\Lms\Enums\CourseStatus;
use CmsOrbit\Lms\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $title = rtrim($this->faker->sentence(4), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1, 999999),
            'subtitle' => $this->faker->sentence(6),
            'description' => $this->faker->paragraphs(3, true),
            'thumbnail' => null,
            'instructor_id' => null,
            'level' => $this->faker->randomElement(CourseLevel::cases())->value,
            'status' => CourseStatus::Draft->value,
            'category' => $this->faker->randomElement(['Development', 'Design', 'Business', 'Marketing']),
            'duration_minutes' => $this->faker->numberBetween(30, 1200),
            'max_students' => null,
            'published_at' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => CourseStatus::Published->value,
            'published_at' => now(),
        ]);
    }
}
