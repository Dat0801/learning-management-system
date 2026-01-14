<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizResult;
use App\Repositories\Interfaces\QuizRepositoryInterface;

class QuizService
{
    public function __construct(
        protected QuizRepositoryInterface $quizRepository
    ) {
    }

    public function getQuizForLesson(int $lessonId): ?Quiz
    {
        return $this->quizRepository->findByLesson($lessonId);
    }

    public function submitQuiz(Quiz $quiz, int $userId, array $answers): QuizResult
    {
        $questions = $quiz->questions()->with('answers')->get();

        $totalQuestions = $questions->count();
        $correct = 0;

        foreach ($questions as $question) {
            $selectedAnswerId = $answers[$question->id] ?? null;
            if (!$selectedAnswerId) {
                continue;
            }

            $isCorrect = $question->answers()
                ->where('id', $selectedAnswerId)
                ->where('is_correct', true)
                ->exists();

            if ($isCorrect) {
                $correct++;
            }
        }

        $score = $totalQuestions > 0 ? (int) round(($correct / $totalQuestions) * 100) : 0;
        $passed = $score >= $quiz->passing_score;

        return QuizResult::create([
            'quiz_id' => $quiz->id,
            'user_id' => $userId,
            'score' => $score,
            'passed' => $passed,
            'submitted_at' => now(),
        ]);
    }
}

