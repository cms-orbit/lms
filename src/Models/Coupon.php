<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Models;

use CmsOrbit\Lms\Database\Factories\CouponFactory;
use CmsOrbit\Lms\Enums\CouponType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    /** @use HasFactory<CouponFactory> */
    use HasFactory;

    protected $table = 'lms_coupons';

    protected $fillable = [
        'code',
        'type',
        'amount',
        'min_order_amount',
        'max_uses',
        'used',
        'starts_at',
        'expires_at',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'type' => CouponType::class,
            'amount' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_uses' => 'integer',
            'used' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    protected static function newFactory(): CouponFactory
    {
        return CouponFactory::new();
    }

    public function isRedeemable(float $orderAmount): bool
    {
        if (! $this->active) {
            return false;
        }

        if ($this->max_uses !== null && $this->used >= $this->max_uses) {
            return false;
        }

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->min_order_amount !== null && $orderAmount < (float) $this->min_order_amount) {
            return false;
        }

        return true;
    }

    /**
     * Discount applied to the given amount, capped at the amount itself.
     */
    public function discountFor(float $amount): float
    {
        $discount = $this->type === CouponType::Percent
            ? $amount * ((float) $this->amount / 100)
            : (float) $this->amount;

        return round(min($discount, $amount), 2);
    }
}
