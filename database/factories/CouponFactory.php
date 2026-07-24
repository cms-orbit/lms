<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Enums\CouponType;
use CmsOrbit\Lms\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'type' => CouponType::Percent->value,
            'amount' => 10,
            'min_order_amount' => null,
            'max_uses' => null,
            'used' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'active' => true,
        ];
    }

    public function fixed(float $amount): static
    {
        return $this->state(fn (): array => [
            'type' => CouponType::Fixed->value,
            'amount' => $amount,
        ]);
    }
}
