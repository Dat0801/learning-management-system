<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlist = $request->user()->wishlist()->with('course.instructor')->get();
        // Return only the courses
        $courses = $wishlist->map(function ($item) {
            return $item->course;
        });

        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $wishlist = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'course_id' => $request->course_id,
        ]);

        return response()->json([
            'message' => 'Course added to wishlist',
            'data' => $wishlist
        ]);
    }

    public function destroy(Request $request, $courseId)
    {
        $deleted = Wishlist::where('user_id', $request->user()->id)
            ->where('course_id', $courseId)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Course removed from wishlist']);
        }

        return response()->json(['message' => 'Course not found in wishlist'], 404);
    }
}
