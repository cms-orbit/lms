<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Services;

use CmsOrbit\Lms\Enums\CourseStatus;
use CmsOrbit\Lms\Enums\EarningStatus;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\CourseQuestion;
use CmsOrbit\Lms\Models\Earning;
use CmsOrbit\Lms\Models\Enrollment;
use CmsOrbit\Lms\Models\Review;
use Illuminate\Database\Eloquent\Model;

/**
 * Aggregates the metrics shown on an instructor's dashboard: catalogue size,
 * student reach, earnings ledger, and recent engagement.
 */
class InstructorDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function forInstructor(Model $instructor): array
    {
        $instructorId = $instructor->getKey();
        $courseIds = Course::query()->where('instructor_id', $instructorId)->pluck('id');

        $earnings = Earning::query()->where('instructor_id', $instructorId);

        return [
            'courses_count' => $courseIds->count(),
            'published_count' => Course::query()
                ->where('instructor_id', $instructorId)
                ->where('status', CourseStatus::Published)
                ->count(),
            'students_count' => Enrollment::query()->whereIn('course_id', $courseIds)->distinct('student_id')->count('student_id'),
            'enrollments_count' => Enrollment::query()->whereIn('course_id', $courseIds)->count(),
            'total_earnings' => round((float) (clone $earnings)->sum('amount'), 2),
            'available_earnings' => round((float) (clone $earnings)->where('status', EarningStatus::Available)->whereNull('payout_id')->sum('amount'), 2),
            'paid_earnings' => round((float) (clone $earnings)->where('status', EarningStatus::Paid)->sum('amount'), 2),
            'courses' => Course::query()
                ->where('instructor_id', $instructorId)
                ->withCount('enrollments')
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (Course $course) => [
                    'id' => $course->id,
                    'slug' => $course->slug,
                    'title' => $course->title,
                    'status' => $course->status?->value,
                    'students' => $course->enrollments_count,
                    'rating' => $course->averageRating(),
                ])
                ->all(),
            'recent_reviews' => Review::query()
                ->whereIn('course_id', $courseIds)
                ->with(['student', 'course'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (Review $review) => [
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'student' => $review->student?->name,
                    'course' => $review->course?->title,
                ])
                ->all(),
            'recent_questions' => CourseQuestion::query()
                ->whereIn('course_id', $courseIds)
                ->with(['author', 'course'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (CourseQuestion $question) => [
                    'title' => $question->title,
                    'author' => $question->author?->name,
                    'course' => $question->course?->title,
                    'resolved' => $question->resolved,
                ])
                ->all(),
        ];
    }
}
