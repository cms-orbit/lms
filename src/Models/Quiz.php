<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\QuizFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    /** @use HasFactory<QuizFactory> */
    use HasFactory;

    protected $table = 'lms_quizzes';

    protected $fillable = [
        'course_id',
        'section_id',
        'title',
        'description',
        'order',
        'pass_mark',
        'time_limit_minutes',
        'max_attempts',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'pass_mark' => 'integer',
            'time_limit_minutes' => 'integer',
            'max_attempts' => 'integer',
        ];
    }

    protected static function newFactory(): QuizFactory
    {
        return QuizFactory::new();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'section_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    /**
     * Highest attainable score across all questions.
     */
    public function totalPoints(): int
    {
        return (int) $this->questions()->sum('points');
    }
}
