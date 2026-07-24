<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\CourseSectionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A course section (called a "topic" in TutorLMS): an ordered grouping of
 * lessons and quizzes inside a course.
 */
class CourseSection extends Model
{
    /** @use HasFactory<CourseSectionFactory> */
    use HasFactory;

    protected $table = 'lms_course_sections';

    protected $fillable = [
        'course_id',
        'title',
        'summary',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    protected static function newFactory(): CourseSectionFactory
    {
        return CourseSectionFactory::new();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'section_id')->orderBy('order');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'section_id')->orderBy('order');
    }
}
