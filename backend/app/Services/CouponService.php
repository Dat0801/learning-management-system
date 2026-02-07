<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Course;
use App\Models\User;
use Exception;

class CouponService
{
    public function validateCoupon(string $code, User $user, ?Course $course = null): array
    {
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            throw new Exception('Invalid coupon code.');
        }

        if (! $coupon->isValid()) {
            throw new Exception('Coupon is not valid or has expired.');
        }

        // Check if coupon is course-specific
        if ($coupon->course_id && $course && $coupon->course_id !== $course->id) {
            throw new Exception('This coupon is not valid for this course.');
        }

        // Check if user has already used this coupon
        if (! $coupon->canBeUsedBy($user)) {
            throw new Exception('You have already used this coupon.');
        }

        return [
            'coupon' => $coupon,
            'valid' => true,
        ];
    }

    public function applyCoupon(string $code, float $amount, User $user, ?Course $course = null): array
    {
        $validation = $this->validateCoupon($code, $user, $course);
        $coupon = $validation['coupon'];

        $discount = $coupon->calculateDiscount($amount);
        $finalAmount = max(0, $amount - $discount);

        return [
            'coupon' => $coupon,
            'original_amount' => $amount,
            'discount_amount' => $discount,
            'final_amount' => $finalAmount,
        ];
    }

    public function recordUsage(Coupon $coupon, User $user, ?int $transactionId, float $discountAmount): CouponUsage
    {
        $usage = CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $user->id,
            'transaction_id' => $transactionId,
            'discount_amount' => $discountAmount,
        ]);

        // Increment used count
        $coupon->increment('used_count');

        return $usage;
    }
}
