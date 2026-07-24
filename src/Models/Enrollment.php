<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\EnrollmentFactory;
use CmsOrbit\Lms\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    /** @use HasFactory<EnrollmentFactory> */
    use HasFactory;

    protected $table = 'lms_enrollments';

    protected $fillable = [
        'course_id',
        'student_id',
        'status',
        'progress',
        'enrolled_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'progress' => 'integer',
            'enrolled_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function newFactory(): EnrollmentFactory
    {
        return EnrollmentFactory::new();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo((string) config('lms.user_model'), 'student_id');
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * Recompute the completion percentage from lesson progress against the
     * course's total lesson count, and flip the enrollment to Completed once
     * every lesson is done.
     */
    public function recalculateProgress(): void
    {
        $totalLessons = Lesson::query()->where('course_id', $this->course_id)->count();

        $completedLessons = $this->lessonProgress()
            ->where('completed', true)
            ->count();

        $progress = $totalLessons > 0
            ? (int) floor($completedLessons / $totalLessons * 100)
            : 0;

        $this->progress = $progress;

        if ($progress >= 100 && $this->status !== EnrollmentStatus::Cancelled) {
            $this->status = EnrollmentStatus::Completed;
            $this->completed_at ??= now();
        } elseif ($progress < 100 && $this->status === EnrollmentStatus::Completed) {
            $this->status = EnrollmentStatus::Active;
            $this->completed_at = null;
        }

        $this->save();
    }
}
