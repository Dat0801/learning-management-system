<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminQuizController extends Controller
{
    // Get Quiz for a Lesson
    public function getQuiz(Lesson $lesson)
    {
        $quiz = $lesson->quiz()->with(['questions.answers'])->first();
        if (!$quiz) {
            return response()->json(null);
        }
        return response()->json($quiz);
    }

    // Create or Update Quiz
    public function storeQuiz(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'passing_score' => 'required|integer|min:0',
        ]);

        $quiz = $lesson->quiz()->updateOrCreate(
            ['lesson_id' => $lesson->id],
            [
                'title' => $request->title,
                'description' => $request->description,
                'passing_score' => $request->passing_score,
            ]
        );

        return response()->json($quiz);
    }

    // Delete Quiz
    public function deleteQuiz(Quiz $quiz)
    {
        $quiz->delete();
        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    // Questions
    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $request->validate([
            'question_text' => 'required|string',
            'order' => 'integer',
        ]);

        $question = $quiz->questions()->create([
            'question_text' => $request->question_text,
            'order' => $request->order ?? 0,
        ]);

        return response()->json($question, 201);
    }

    public function updateQuestion(Request $request, QuizQuestion $question)
    {
        $request->validate([
            'question_text' => 'string',
            'order' => 'integer',
        ]);

        $question->update($request->all());
        return response()->json($question);
    }

    public function deleteQuestion(QuizQuestion $question)
    {
        $question->delete();
        return response()->json(['message' => 'Question deleted successfully']);
    }

    // Answers
    public function storeAnswer(Request $request, QuizQuestion $question)
    {
        $request->validate([
            'answer_text' => 'required|string',
            'is_correct' => 'boolean',
        ]);

        $answer = $question->answers()->create([
            'answer_text' => $request->answer_text,
            'is_correct' => $request->is_correct ?? false,
        ]);

        return response()->json($answer, 201);
    }

    public function updateAnswer(Request $request, QuizAnswer $answer)
    {
        $request->validate([
            'answer_text' => 'string',
            'is_correct' => 'boolean',
        ]);

        $answer->update($request->all());
        return response()->json($answer);
    }

    public function deleteAnswer(QuizAnswer $answer)
    {
        $answer->delete();
        return response()->json(['message' => 'Answer deleted successfully']);
    }
}
