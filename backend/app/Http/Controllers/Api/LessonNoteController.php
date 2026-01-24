<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonNoteController extends Controller
{
    public function show(Lesson $lesson)
    {
        $note = $lesson->notes()->where('user_id', auth()->id())->first();
        return response()->json($note);
    }

    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $note = $lesson->notes()->updateOrCreate(
            ['user_id' => auth()->id()],
            ['content' => $request->input('content')]
        );

        return response()->json($note);
    }
}
