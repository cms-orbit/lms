<?php

declare(strict_types=1);

namespace CmsOrbit\Lms\Services;

use CmsOrbit\Lms\Enums\CourseStatus;
use CmsOrbit\Lms\Enums\EarningStatus;
use CmsOrbit\Lms\Enums\EnrollmentStatus;
use CmsOrbit\Lms\Enums\OrderStatus;
use CmsOrbit\Lms\Models\Coupon;
use CmsOrbit\Lms\Models\Course;
use CmsOrbit\Lms\Models\Earning;
use CmsOrbit\Lms\Models\Enrollment;
use CmsOrbit\Lms\Models\Order;
use CmsOrbit\Lms\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Marketplace checkout: turns a student's cart of courses into an order, splits
 * revenue between each course's instructor and the platform, and provisions
 * enrollments once the order is paid.
 */
class CheckoutService
{
    /**
     * Build a pending order for the given courses. Zero-total orders (all free,
     * or fully discounted) are settled immediately.
     *
     * @param  array<int, int|Course>  $courses
     */
    public function checkout(Model $student, array $courses, ?string $couponCode = null, string $paymentMethod = 'manual'): Order
    {
        return DB::transaction(function () use ($student, $courses, $couponCode, $paymentMethod): Order {
            $models = collect($courses)
                ->map(fn ($course) => $course instanceof Course ? $course : Course::query()->findOrFail($course))
                ->unique('id')
                ->values();

            $subtotal = round($models->sum(fn (Course $course) => $course->effectivePrice()), 2);

            $coupon = $couponCode !== null ? Coupon::query()->where('code', $couponCode)->first() : null;
            $discount = $coupon !== null && $coupon->isRedeemable($subtotal) ? $coupon->discountFor($subtotal) : 0.0;
            $total = round(max($subtotal - $discount, 0), 2);

            $order = Order::create([
                'student_id' => $student->getKey(),
                'status' => OrderStatus::Pending,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'currency' => (string) ($models->first()?->currency ?? config('lms.marketplace.currency', 'USD')),
                'coupon_id' => $discount > 0 ? $coupon?->getKey() : null,
                'payment_method' => $paymentMethod,
            ]);

            foreach ($models as $course) {
                $unitPrice = round($course->effectivePrice(), 2);
                $lineNet = $subtotal > 0 ? round($unitPrice - $discount * ($unitPrice / $subtotal), 2) : 0.0;
                $rate = $course->commissionRate();
                $instructorEarning = round($lineNet * $rate / 100, 2);

                OrderItem::create([
                    'order_id' => $order->id,
                    'course_id' => $course->id,
                    'instructor_id' => $course->instructor_id,
                    'unit_price' => $unitPrice,
                    'commission_rate' => $rate,
                    'instructor_earning' => $instructorEarning,
                    'admin_earning' => round($lineNet - $instructorEarning, 2),
                ]);
            }

            if ($total <= 0.0) {
                $this->markPaid($order->fresh('items'));
            }

            return $order->fresh(['items', 'coupon']);
        });
    }

    /**
     * Settle an order: record instructor earnings, redeem the coupon, and enroll
     * the student in every purchased course. Idempotent for already-paid orders.
     */
    public function markPaid(Order $order): Order
    {
        if ($order->isPaid()) {
            return $order;
        }

        return DB::transaction(function () use ($order): Order {
            $order->forceFill([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
            ])->save();

            $holdDays = (int) config('lms.marketplace.hold_days', 0);

            foreach ($order->items as $item) {
                if ($item->instructor_id !== null && (float) $item->instructor_earning > 0) {
                    Earning::create([
                        'instructor_id' => $item->instructor_id,
                        'order_item_id' => $item->id,
                        'course_id' => $item->course_id,
                        'amount' => $item->instructor_earning,
                        'status' => $holdDays > 0 ? EarningStatus::Pending : EarningStatus::Available,
                        'available_at' => now()->addDays($holdDays),
                    ]);
                }

                $this->enroll($order->student_id, (int) $item->course_id);
            }

            if ($order->coupon !== null) {
                $order->coupon->increment('used');
            }

            return $order->fresh(['items']);
        });
    }

    /**
     * Enroll a student in a free course without an order.
     */
    public function enrollFree(Model $student, Course $course): Enrollment
    {
        abort_unless($course->is_free, 422, 'Course is not free.');

        return $this->enroll((int) $student->getKey(), (int) $course->id);
    }

    protected function enroll(int $studentId, int $courseId): Enrollment
    {
        return Enrollment::query()->firstOrCreate(
            ['course_id' => $courseId, 'student_id' => $studentId],
            ['status' => EnrollmentStatus::Active, 'progress' => 0, 'enrolled_at' => now()],
        );
    }

    /**
     * Refund a paid order: reverse earnings and cancel enrollments.
     */
    public function refund(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $order->forceFill(['status' => OrderStatus::Refunded])->save();

            foreach ($order->items as $item) {
                Earning::query()
                    ->where('order_item_id', $item->id)
                    ->whereNull('payout_id')
                    ->update(['status' => EarningStatus::Refunded]);

                Enrollment::query()
                    ->where('course_id', $item->course_id)
                    ->where('student_id', $order->student_id)
                    ->update(['status' => EnrollmentStatus::Cancelled]);
            }

            return $order->fresh(['items']);
        });
    }

    /**
     * Only published courses can be purchased.
     */
    public function assertPurchasable(Course $course): void
    {
        abort_unless($course->status === CourseStatus::Published, 404);
    }
}
