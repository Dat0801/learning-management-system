<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonResource;
use Illuminate\Http\Request;

class AdminLessonResourceController extends Controller
{
    public function index(Lesson $lesson)
    {
        return response()->json($lesson->resources);
    }

    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|string', // In real app, this would be a file upload handling
            'type' => 'required|string',
        ]);

        $resource = $lesson->resources()->create($request->all());
        return response()->json($resource, 201);
    }

    public function destroy(LessonResource $resource)
    {
        $resource->delete();
        return response()->json(['message' => 'Resource deleted successfully'], 204);
    }
}
