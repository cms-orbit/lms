<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\AssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    /** @use HasFactory<AssignmentFactory> */
    use HasFactory;

    protected $table = 'lms_assignments';

    protected $fillable = [
        'course_id',
        'section_id',
        'title',
        'instructions',
        'max_points',
        'pass_points',
        'due_at',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'max_points' => 'integer',
            'pass_points' => 'integer',
            'due_at' => 'datetime',
            'order' => 'integer',
        ];
    }

    protected static function newFactory(): AssignmentFactory
    {
        return AssignmentFactory::new();
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
