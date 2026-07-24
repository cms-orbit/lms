<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\CourseQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<CourseQuestion>
 */
class CourseQuestionFactory extends Factory
{
    protected $model = CourseQuestion::class;

    public function definition(): array
    {
        /** @var class-string<Model> $userModel */
        $userModel = (string) config('lms.user_model');

        return [
            'course_id' => Course::factory(),
            'user_id' => $userModel::factory(),
            'title' => rtrim($this->faker->sentence(5), '.'),
            'body' => $this->faker->paragraph(),
            'resolved' => false,
        ];
    }
}
