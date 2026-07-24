<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseAnswer extends Model
{
    protected $table = 'lms_course_answers';

    protected $fillable = [
        'question_id',
        'user_id',
        'body',
        'is_instructor',
    ];

    protected function casts(): array
    {
        return [
            'is_instructor' => 'boolean',
        ];
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(CourseQuestion::class, 'question_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo((string) config('lms.user_model'), 'user_id');
    }
}
