<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\LessonFactory;
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
    ];

    protected function casts(): array
    {
        return [
            'type' => LessonType::class,
            'duration_seconds' => 'integer',
            'order' => 'integer',
            'is_preview' => 'boolean',
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
