<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createPaymentIntent(Request $request, $courseId)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'coupon_code' => 'nullable|string',
        ]);

        try {
            $course = Course::findOrFail($courseId);
            $user = $request->user();

            $transaction = $this->paymentService->createPaymentIntent(
                $user,
                $course,
                $request->coupon_code
            );

            $paymentDetails = $transaction->payment_details ?? [];

            return response()->json([
                'success' => true,
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'amount' => $transaction->amount,
                    'original_amount' => $paymentDetails['original_amount'] ?? $transaction->amount,
                    'discount_amount' => $paymentDetails['discount_amount'] ?? 0,
                    'currency' => $transaction->currency,
                    'status' => $transaction->status,
                    'payment_intent_id' => $transaction->payment_intent_id,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function confirmPayment(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required|string',
            'payment_intent_id' => 'nullable|string',
        ]);

        try {
            $transaction = $this->paymentService->confirmPayment(
                $request->transaction_id,
                $request->only(['payment_intent_id', 'payment_method'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment confirmed successfully',
                'data' => $transaction,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function myTransactions(Request $request)
    {
        $transactions = $this->paymentService->getUserTransactions($request->user());

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
}
