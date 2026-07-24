<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Enums\EarningStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Earning extends Model
{
    protected $table = 'lms_earnings';

    protected $fillable = [
        'instructor_id',
        'order_item_id',
        'course_id',
        'payout_id',
        'amount',
        'status',
        'available_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => EarningStatus::class,
            'available_at' => 'datetime',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo((string) config('lms.user_model'), 'instructor_id');
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function payout(): BelongsTo
    {
        return $this->belongsTo(Payout::class);
    }

    /**
     * @param  Builder<Earning>  $query
     * @return Builder<Earning>
     */
    public function scopePayable(Builder $query): Builder
    {
        return $query->where('status', EarningStatus::Available)->whereNull('payout_id');
    }
}
