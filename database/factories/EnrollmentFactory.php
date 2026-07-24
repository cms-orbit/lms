<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Enums\EnrollmentStatus;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Enrollment>
 */
class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        /** @var class-string<Model> $userModel */
        $userModel = (string) config('lms.user_model');

        return [
            'course_id' => Course::factory(),
            'student_id' => $userModel::factory(),
            'status' => EnrollmentStatus::Active->value,
            'progress' => 0,
            'enrolled_at' => now(),
            'completed_at' => null,
        ];
    }
}
