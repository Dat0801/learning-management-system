<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\PaymentConfirmation;
use Exception;
use Illuminate\Support\Str;

class PaymentService
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function createPaymentIntent(User $user, Course $course, ?string $couponCode = null): Transaction
    {
        // Check if course is free
        if ($course->price == 0) {
            return $this->createFreeTransaction($user, $course);
        }

        // Check if user already has a pending or completed transaction for this course
        $existingTransaction = Transaction::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['pending', 'completed'])
            ->first();

        if ($existingTransaction) {
            throw new Exception('Transaction already exists for this course.');
        }

        // Calculate final amount with coupon if provided
        $originalAmount = $course->price;
        $finalAmount = $originalAmount;
        $discountAmount = 0;
        $couponId = null;

        if ($couponCode) {
            try {
                $couponResult = $this->couponService->applyCoupon($couponCode, $originalAmount, $user, $course);
                $finalAmount = $couponResult['final_amount'];
                $discountAmount = $couponResult['discount_amount'];
                $couponId = $couponResult['coupon']->id;
            } catch (Exception $e) {
                throw new Exception('Invalid coupon: '.$e->getMessage());
            }
        }

        // Generate unique transaction ID
        $transactionId = 'TXN-'.strtoupper(Str::random(12));

        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'transaction_id' => $transactionId,
            'amount' => $finalAmount,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'stripe',
            'payment_details' => [
                'original_amount' => $originalAmount,
                'discount_amount' => $discountAmount,
                'coupon_code' => $couponCode,
                'coupon_id' => $couponId,
            ],
        ]);

        // In a real implementation, you would create a Stripe PaymentIntent here
        // For now, we'll return the transaction with a placeholder payment_intent_id
        // $paymentIntent = \Stripe\PaymentIntent::create([...]);
        // $transaction->update(['payment_intent_id' => $paymentIntent->id]);

        return $transaction;
    }

    public function createFreeTransaction(User $user, Course $course): Transaction
    {
        $transactionId = 'TXN-FREE-'.strtoupper(Str::random(12));

        return Transaction::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'transaction_id' => $transactionId,
            'amount' => 0,
            'currency' => 'USD',
            'status' => 'completed',
            'payment_method' => 'free',
            'paid_at' => now(),
        ]);
    }

    public function confirmPayment(string $transactionId, array $paymentDetails = []): Transaction
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        if ($transaction->isCompleted()) {
            throw new Exception('Transaction already completed.');
        }

        $transaction->update([
            'status' => 'completed',
            'payment_intent_id' => $paymentDetails['payment_intent_id'] ?? null,
            'payment_details' => $paymentDetails,
            'paid_at' => now(),
        ]);

        $transaction = $transaction->fresh();
        $course = $transaction->course;
        $user = $transaction->user;

        // Record coupon usage if coupon was used
        $paymentDetails = $transaction->payment_details ?? [];
        if (isset($paymentDetails['coupon_id']) && $paymentDetails['coupon_id']) {
            $coupon = Coupon::find($paymentDetails['coupon_id']);
            if ($coupon && isset($paymentDetails['discount_amount'])) {
                $this->couponService->recordUsage(
                    $coupon,
                    $user,
                    $transaction->id,
                    $paymentDetails['discount_amount']
                );
            }
        }

        // Send payment confirmation notification
        if ($transaction->amount > 0) {
            $user->notify(new PaymentConfirmation($transaction, $course));
        }

        return $transaction;
    }

    public function failPayment(string $transactionId, string $reason = ''): Transaction
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        $transaction->update([
            'status' => 'failed',
            'payment_details' => array_merge($transaction->payment_details ?? [], ['failure_reason' => $reason]),
        ]);

        return $transaction->fresh();
    }

    public function getUserTransactions(User $user)
    {
        return Transaction::where('user_id', $user->id)
            ->with('course')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
