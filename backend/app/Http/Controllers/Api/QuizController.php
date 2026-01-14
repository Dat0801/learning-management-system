<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quiz\SubmitQuizRequest;
use App\Http\Resources\QuizResource;
use App\Http\Resources\QuizResultResource;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Services\QuizService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function __construct(protected QuizService $quizService)
    {
    }

    public function showByLesson(Lesson $lesson)
    {
        $quiz = $this->quizService->getQuizForLesson($lesson->id);

        if (! $quiz) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz not found for this lesson',
            ], 404);
        }

        $quiz->load('questions.answers');

        return response()->json([
            'success' => true,
            'data' => new QuizResource($quiz),
        ]);
    }

    public function submit(SubmitQuizRequest $request, Quiz $quiz)
    {
        $user = $request->user();

        $result = $this->quizService->submitQuiz($quiz, $user->id, $request->input('answers', []));

        return response()->json([
            'success' => true,
            'message' => 'Quiz submitted',
            'data' => new QuizResultResource($result),
        ]);
    }
}

