<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EnrollmentResource;
use App\Services\EnrollmentService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    public function store(Request $request, $courseId)
    {
        try {
            $enrollment = $this->enrollmentService->enrollUser($request->user()->id, $courseId);
            return response()->json([
                'success' => true,
                'message' => 'Enrolled successfully',
                'data' => new EnrollmentResource($enrollment->load('course.instructor')),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function myEnrollments(Request $request)
    {
        $enrollments = $this->enrollmentService->getUserEnrollments($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => EnrollmentResource::collection($enrollments),
        ]);
    }
}
