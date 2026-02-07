<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Course;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    protected $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'course_id' => 'nullable|exists:courses,id',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $user = $request->user();
            $course = $request->course_id ? Course::find($request->course_id) : null;

            $result = $this->couponService->applyCoupon(
                $request->code,
                $request->amount,
                $user,
                $course
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function index(Request $request)
    {
        $query = Coupon::query();

        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $coupons = $query->with('course')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($coupons);
    }

    public function show($id)
    {
        $coupon = Coupon::with('course', 'usages.user')->findOrFail($id);

        return response()->json($coupon);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $coupon = Coupon::create($request->all());

        return response()->json($coupon, 201);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code' => 'sometimes|string|max:50|unique:coupons,code,'.$id,
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:percentage,fixed',
            'value' => 'sometimes|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'sometimes|boolean',
            'course_id' => 'nullable|exists:courses,id',
        ]);

        $coupon->update($request->all());

        return response()->json($coupon);
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted successfully'], 204);
    }
}
