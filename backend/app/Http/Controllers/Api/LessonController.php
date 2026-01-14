<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show(Lesson $lesson)
    {
        $user = auth()->user();
        $course = $lesson->course;
        
        $hasEnrollment = $user->enrollments()->where('course_id', $course->id)->exists();
        
        if (!$hasEnrollment) {
            return response()->json(['message' => 'You are not enrolled in this course.'], 403);
        }

        $lesson->is_completed = LessonCompletion::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        return response()->json($lesson);
    }

    public function complete(Request $request, Lesson $lesson)
    {
        $user = $request->user();
        
        // Ensure user is enrolled in the course
        if (!$user->enrollments()->where('course_id', $lesson->course_id)->exists()) {
             return response()->json(['message' => 'Not enrolled'], 403);
        }

        LessonCompletion::firstOrCreate([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id
        ]);

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
