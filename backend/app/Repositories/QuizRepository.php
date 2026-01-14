<?php

namespace App\Repositories;

use App\Models\Quiz;
use App\Repositories\Interfaces\QuizRepositoryInterface;

class QuizRepository implements QuizRepositoryInterface
{
    public function findByLesson(int $lessonId)
    {
        return Quiz::with(['questions.answers'])
            ->where('lesson_id', $lessonId)
            ->first();
    }

    public function find(int $id)
    {
        return Quiz::with(['questions.answers'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Quiz::create($data);
    }

    public function update(int $id, array $data)
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->update($data);
        return $quiz;
    }

    public function delete(int $id)
    {
        return Quiz::destroy($id);
    }
}

