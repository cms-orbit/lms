<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    protected $table = 'lms_certificates';

    protected $fillable = [
        'enrollment_id',
        'course_id',
        'student_id',
        'serial',
        'template',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Certificate $certificate): void {
            if (blank($certificate->serial)) {
                $certificate->serial = 'CERT-'.strtoupper(Str::random(12));
            }

            $certificate->issued_at ??= now();
        });
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo((string) config('lms.user_model'), 'student_id');
    }
}
