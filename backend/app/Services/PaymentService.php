<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\PaymentConfirmation;
use Exception;
use Illuminate\Support\Str;

class PaymentService
{
    public function createPaymentIntent(User $user, Course $course): Transaction
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

        // Generate unique transaction ID
        $transactionId = 'TXN-'.strtoupper(Str::random(12));

        // Create transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'transaction_id' => $transactionId,
            'amount' => $course->price,
            'currency' => 'USD',
            'status' => 'pending',
            'payment_method' => 'stripe',
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
