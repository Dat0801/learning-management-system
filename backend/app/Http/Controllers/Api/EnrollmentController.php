<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            return response()->json($enrollment, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function myEnrollments(Request $request)
    {
        return response()->json($this->enrollmentService->getUserEnrollments($request->user()->id));
    }
}
