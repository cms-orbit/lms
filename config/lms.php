<?php

declare(strict_types=1);
use App\Models\User;

return [
    /*
    |--------------------------------------------------------------------------
    | User model
    |--------------------------------------------------------------------------
    |
    | Instructors and students are regular Orbit users. The LMS references
    | whatever user model the application authenticates with, so it works on a
    | plain Laravel host without assuming a package-specific user class.
    |
    */
    'user_model' => env('LMS_USER_MODEL', config('auth.providers.users.model', User::class)),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'quiz_pass_mark' => 70,
        'course_level' => 'all_levels',
    ],
];
