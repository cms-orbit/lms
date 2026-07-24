<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    protected $table = 'lms_assignment_submissions';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'attachment',
        'status',
        'points',
        'feedback',
        'submitted_at',
        'graded_at',
    ];

    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'submitted_at' => 'datetime',
            'graded_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo((string) config('lms.user_model'), 'student_id');
    }

    public function isPassing(): bool
    {
        if ($this->points === null) {
            return false;
        }

        return $this->points >= (int) $this->assignment?->pass_points;
    }
}
