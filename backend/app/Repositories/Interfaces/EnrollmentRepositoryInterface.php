<?php

namespace App\Repositories\Interfaces;

interface EnrollmentRepositoryInterface
{
    public function create(array $data);
    public function findByCourseAndUser($courseId, $userId);
    public function getUserEnrollments($userId);
}
