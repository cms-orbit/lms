<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\CourseQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseQuestion extends Model
{
    /** @use HasFactory<CourseQuestionFactory> */
    use HasFactory;

    protected $table = 'lms_course_questions';

    protected $fillable = [
        'course_id',
        'lesson_id',
        'user_id',
        'title',
        'body',
        'resolved',
    ];

    protected function casts(): array
    {
        return [
            'resolved' => 'boolean',
        ];
    }

    protected static function newFactory(): CourseQuestionFactory
    {
        return CourseQuestionFactory::new();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo((string) config('lms.user_model'), 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(CourseAnswer::class, 'question_id')->oldest();
    }
}
