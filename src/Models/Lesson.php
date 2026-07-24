<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use Carbon\CarbonInterface;
use CmsOrbit\Lms\Database\Factories\LessonFactory;
use CmsOrbit\Lms\Enums\DripType;
use CmsOrbit\Lms\Enums\LessonType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Lesson extends Model
{
    /** @use HasFactory<LessonFactory> */
    use HasFactory;

    protected $table = 'lms_lessons';

    protected $fillable = [
        'course_id',
        'section_id',
        'title',
        'slug',
        'type',
        'content',
        'video_url',
        'video_provider',
        'duration_seconds',
        'order',
        'is_preview',
        'drip_days',
        'drip_date',
        'drip_prerequisite_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => LessonType::class,
            'duration_seconds' => 'integer',
            'order' => 'integer',
            'is_preview' => 'boolean',
            'drip_days' => 'integer',
            'drip_date' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Lesson $lesson): void {
            if (blank($lesson->slug) && filled($lesson->title)) {
                $lesson->slug = static::uniqueSlug((string) $lesson->title, (int) $lesson->course_id);
            }
        });
    }

    protected static function newFactory(): LessonFactory
    {
        return LessonFactory::new();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function prerequisite(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'drip_prerequisite_id');
    }

    /**
     * The moment this lesson unlocks for an enrollment under date/days drip,
     * or null when unlocking is immediate or condition-based (sequential /
     * prerequisite).
     */
    public function unlockDateFor(Enrollment $enrollment): ?CarbonInterface
    {
        $course = $this->relationLoaded('course') ? $this->course : $this->course()->first();

        if ($course === null || ! $course->drip_enabled) {
            return null;
        }

        return match ($course->drip_type) {
            DripType::AfterDays => $this->drip_days !== null
                ? ($enrollment->enrolled_at ?? $enrollment->created_at)?->copy()->addDays((int) $this->drip_days)
                : null,
            DripType::ByDate => $this->drip_date,
            default => null,
        };
    }

    /**
     * Whether the lesson is accessible to a student given the course drip rules.
     * Free previews are always accessible.
     */
    public function isUnlockedFor(Enrollment $enrollment): bool
    {
        if ($this->is_preview) {
            return true;
        }

        $course = $this->relationLoaded('course') ? $this->course : $this->course()->first();

        if ($course === null || ! $course->drip_enabled) {
            return true;
        }

        return match ($course->drip_type) {
            DripType::AfterDays, DripType::ByDate => ($date = $this->unlockDateFor($enrollment)) === null || $date->isPast(),
            DripType::Sequential => ! Lesson::query()
                ->where('course_id', $this->course_id)
                ->where('order', '<', $this->order)
                ->whereNotIn('id', $this->completedLessonIds($enrollment))
                ->exists(),
            DripType::Prerequisite => $this->drip_prerequisite_id === null
                || in_array((int) $this->drip_prerequisite_id, $this->completedLessonIds($enrollment), true),
            default => true,
        };
    }

    /**
     * @return array<int, int>
     */
    protected function completedLessonIds(Enrollment $enrollment): array
    {
        return $enrollment->lessonProgress()
            ->where('completed', true)
            ->pluck('lesson_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public static function uniqueSlug(string $title, int $courseId, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'lesson';
        $slug = $base;
        $counter = 1;

        while (
            static::query()
                ->where('course_id', $courseId)
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$counter);
        }

        return $slug;
    }
}
