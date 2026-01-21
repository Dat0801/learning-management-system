<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;

class AdminLessonController extends Controller
{
    public function index(Course $course)
    {
        return response()->json($course->lessons()->orderBy('order')->get());
    }

    public function store(Request $request, Course $course)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string',
            'duration' => 'nullable|string',
            'is_preview' => 'boolean',
            'order' => 'integer'
        ]);

        $lesson = $course->lessons()->create($request->all());
        return response()->json($lesson, 201);
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'Lesson does not belong to this course'], 404);
        }

        $request->validate([
            'title' => 'string|max:255',
            'content' => 'nullable|string',
            'video_url' => 'nullable|string',
            'duration' => 'nullable|string',
            'is_preview' => 'boolean',
            'order' => 'integer'
        ]);

        $lesson->update($request->all());
        return response()->json($lesson);
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        if ($lesson->course_id !== $course->id) {
            return response()->json(['message' => 'Lesson does not belong to this course'], 404);
        }

        $lesson->delete();
        return response()->json(['message' => 'Lesson deleted successfully'], 204);
    }
}
