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

    /*
    |--------------------------------------------------------------------------
    | Marketplace
    |--------------------------------------------------------------------------
    |
    | Revenue split and settlement rules for the open-marketplace model, where
    | independent instructors sell courses and the platform takes a commission.
    |
    | - commission_rate: instructor's share of each sale, as a percentage.
    | - hold_days: days an earning stays "pending" before it becomes payable
    |   (a refund window). 0 makes earnings immediately available.
    |
    */
    'marketplace' => [
        'currency' => env('LMS_CURRENCY', 'USD'),
        'commission_rate' => (int) env('LMS_COMMISSION_RATE', 80),
        'hold_days' => (int) env('LMS_EARNING_HOLD_DAYS', 0),
    ],
];
