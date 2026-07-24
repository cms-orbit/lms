<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Database\Factories;

use CmsOrbit\Lms\Enums\OrderStatus;
use CmsOrbit\Lms\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        /** @var class-string<Model> $userModel */
        $userModel = (string) config('lms.user_model');

        return [
            'student_id' => $userModel::factory(),
            'status' => OrderStatus::Pending->value,
            'subtotal' => 0,
            'discount' => 0,
            'total' => 0,
            'currency' => 'USD',
            'payment_method' => 'manual',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => OrderStatus::Paid->value,
            'paid_at' => now(),
        ]);
    }
}
