<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\CourseFactory;
use CmsOrbit\Lms\Enums\CourseLevel;
use CmsOrbit\Lms\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Course extends Model
{
    /** @use HasFactory<CourseFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'lms_courses';

    protected $fillable = [
        'title',
        'slug',
        'subtitle',
        'description',
        'thumbnail',
        'instructor_id',
        'level',
        'status',
        'category',
        'duration_minutes',
        'max_students',
        'published_at',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'level' => CourseLevel::class,
            'status' => CourseStatus::class,
            'duration_minutes' => 'integer',
            'max_students' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Course $course): void {
            if (blank($course->slug) && filled($course->title)) {
                $course->slug = static::uniqueSlug((string) $course->title);
            }
        });
    }

    protected static function newFactory(): CourseFactory
    {
        return CourseFactory::new();
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo((string) config('lms.user_model'), 'instructor_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class)->orderBy('order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * @param  Builder<Course>  $query
     * @return Builder<Course>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', CourseStatus::Published)
            ->where(function (Builder $builder): void {
                $builder->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function isPublished(): bool
    {
        if ($this->status !== CourseStatus::Published) {
            return false;
        }

        return $this->published_at === null || $this->published_at->lte(now());
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $counter = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$counter);
        }

        return $slug;
    }
}
