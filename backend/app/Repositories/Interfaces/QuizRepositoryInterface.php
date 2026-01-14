<?php

namespace App\Repositories\Interfaces;

interface QuizRepositoryInterface
{
    public function findByLesson(int $lessonId);

    public function find(int $id);

    public function create(array $data);

    public function update(int $id, array $data);

    public function delete(int $id);
}

