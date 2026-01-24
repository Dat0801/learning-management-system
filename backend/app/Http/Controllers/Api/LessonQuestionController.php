<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonQuestion;
use Illuminate\Http\Request;

class LessonQuestionController extends Controller
{
    public function index(Lesson $lesson)
    {
        $questions = $lesson->questions()
            ->with(['user', 'answers.user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json($questions);
    }

    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $question = $lesson->questions()->create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'content' => $request->input('content'),
        ]);

        return response()->json($question->load('user'), 201);
    }

    public function answer(Request $request, LessonQuestion $question)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $answer = $question->answers()->create([
            'user_id' => auth()->id(),
            'content' => $request->input('content'),
        ]);

        return response()->json($answer->load('user'), 201);
    }
}
