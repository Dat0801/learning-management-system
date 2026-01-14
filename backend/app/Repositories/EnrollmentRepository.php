<?php

namespace App\Repositories;

use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function create(array $data)
    {
        return Enrollment::create($data);
    }

    public function findByCourseAndUser($courseId, $userId)
    {
        return Enrollment::where('course_id', $courseId)
                         ->where('user_id', $userId)
                         ->first();
    }

    public function getUserEnrollments($userId)
    {
        return Enrollment::where('user_id', $userId)
            ->with('course.instructor')
            ->get();
    }
}
