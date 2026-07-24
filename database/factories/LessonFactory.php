<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Enums\LessonType;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(LessonType::cases());

        return [
            'course_id' => Course::factory(),
            'section_id' => null,
            'title' => rtrim($this->faker->sentence(4), '.'),
            'type' => $type->value,
            'content' => $type === LessonType::Text ? $this->faker->paragraphs(2, true) : null,
            'video_url' => $type === LessonType::Video ? $this->faker->url() : null,
            'video_provider' => $type === LessonType::Video ? 'youtube' : null,
            'duration_seconds' => $this->faker->numberBetween(60, 3600),
            'order' => $this->faker->numberBetween(0, 20),
            'is_preview' => false,
        ];
    }
}
