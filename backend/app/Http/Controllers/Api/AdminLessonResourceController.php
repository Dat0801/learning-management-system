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
            'url' => 'nullable|string',
            'file' => 'nullable|file|max:51200', // 50MB max
            'type' => 'required|string|in:file,link,pdf,video',
        ]);

        $data = [
            'title' => $request->title,
            'type' => $request->type,
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = \Illuminate\Support\Str::uuid().'.'.$file->getClientOriginalExtension();
            $folder = 'lesson-resources';
            $path = $file->storeAs($folder, $filename, 'public');
            $data['url'] = \Illuminate\Support\Facades\Storage::url($path);
        } else {
            $data['url'] = $request->url;
        }

        $resource = $lesson->resources()->create($data);

        return response()->json($resource, 201);
    }

    public function destroy(LessonResource $resource)
    {
        $resource->delete();

        return response()->json(['message' => 'Resource deleted successfully'], 204);
    }
}
