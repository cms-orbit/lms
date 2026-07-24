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
        'is_free',
        'price',
        'sale_price',
        'currency',
        'commission_rate',
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
            'is_free' => 'boolean',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'commission_rate' => 'integer',
            'duration_minutes' => 'integer',
            'max_students' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Price actually charged: the sale price when set, otherwise the list price.
     * Free courses always resolve to 0.
     */
    public function effectivePrice(): float
    {
        if ($this->is_free) {
            return 0.0;
        }

        $sale = $this->sale_price !== null ? (float) $this->sale_price : null;

        if ($sale !== null && $sale > 0 && $sale < (float) $this->price) {
            return $sale;
        }

        return (float) $this->price;
    }

    public function isPurchasable(): bool
    {
        return ! $this->is_free && $this->effectivePrice() > 0;
    }

    /**
     * Instructor commission percentage for this course, falling back to the
     * marketplace default when the course does not override it.
     */
    public function commissionRate(): int
    {
        if ($this->commission_rate !== null) {
            return (int) $this->commission_rate;
        }

        return (int) config('lms.marketplace.commission_rate', 80);
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

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CourseQuestion::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class)->orderBy('order');
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * Average approved review rating, rounded to one decimal place.
     */
    public function averageRating(): float
    {
        return round((float) $this->reviews()->approved()->avg('rating'), 1);
    }

    public function reviewsCount(): int
    {
        return $this->reviews()->approved()->count();
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
