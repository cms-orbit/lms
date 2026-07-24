<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    protected $table = 'lms_order_items';

    protected $fillable = [
        'order_id',
        'course_id',
        'instructor_id',
        'unit_price',
        'commission_rate',
        'instructor_earning',
        'admin_earning',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'commission_rate' => 'integer',
            'instructor_earning' => 'decimal:2',
            'admin_earning' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo((string) config('lms.user_model'), 'instructor_id');
    }

    public function earning(): HasOne
    {
        return $this->hasOne(Earning::class);
    }
}
