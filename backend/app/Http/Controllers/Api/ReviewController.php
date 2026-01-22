<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index($courseId)
    {
        $reviews = Review::where('course_id', $courseId)
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json($reviews);
    }

    public function store(Request $request, $courseId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $user = Auth::user();
        $course = Course::findOrFail($courseId);

        // Check if user is enrolled
        if (!$course->enrollments()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You must be enrolled to review this course'], 403);
        }

        // Check if already reviewed
        if ($course->reviews()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You have already reviewed this course'], 409);
        }

        $review = $course->reviews()->create([
            'user_id' => $user->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json($review->load('user:id,name'), 201);
    }

    public function destroy(Review $review)
    {
        $user = Auth::user();

        if ($user->id !== $review->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }
}
