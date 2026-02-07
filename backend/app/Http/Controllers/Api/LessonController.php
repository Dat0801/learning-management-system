<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function show(Lesson $lesson)
    {
        $user = auth()->user();
        $course = $lesson->course;

        // Allow access if it's a preview lesson
        if ($lesson->is_preview) {
            $lesson->is_completed = false; // Default for non-enrolled
            $lesson->has_quiz = $lesson->quiz()->exists();

            if ($user) {
                // Check completion if user is logged in
                $lesson->is_completed = LessonCompletion::where('user_id', $user->id)
                    ->where('lesson_id', $lesson->id)
                    ->exists();
            }

            $lesson->load('resources');

            return response()->json($lesson);
        }

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $hasEnrollment = $user->enrollments()->where('course_id', $course->id)->exists();

        if (! $hasEnrollment) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $lesson->is_completed = LessonCompletion::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        $lesson->has_quiz = $lesson->quiz()->exists();
        $lesson->load('resources');

        return response()->json($lesson);
    }

    public function complete(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        // Ensure user is enrolled in the course
        if (! $user->enrollments()->where('course_id', $lesson->course_id)->exists()) {
            return response()->json(['message' => 'Not enrolled'], 403);
        }

        LessonCompletion::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
        ]);

        // Check if course is completed and generate certificate
        try {
            $course = $lesson->course;
            $this->certificateService->checkAndGenerateCertificate($user, $course);
        } catch (\Exception $e) {
            // Log error but don't fail the request
            \Log::warning('Certificate generation failed: '.$e->getMessage());
        }

        return response()->json(['message' => 'Lesson marked as completed', 'is_completed' => true]);
    }

    public function incomplete(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        LessonCompletion::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->delete();

        return response()->json(['message' => 'Lesson marked as incomplete', 'is_completed' => false]);
    }
}
