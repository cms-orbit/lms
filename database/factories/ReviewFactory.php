<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        /** @var class-string<Model> $userModel */
        $userModel = (string) config('lms.user_model');

        return [
            'course_id' => Course::factory(),
            'student_id' => $userModel::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->sentence(12),
            'approved' => true,
        ];
    }
}
