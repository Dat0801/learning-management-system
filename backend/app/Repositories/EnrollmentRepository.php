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
        $enrollments = Enrollment::where('user_id', $userId)
                         ->with('course.instructor')
                         ->get();

        return $enrollments->map(function ($enrollment) {
            $course = $enrollment->course;
            $course->is_enrolled = true;
            // $course->progress = $enrollment->progress ?? 0; 
            return $course;
        });
    }
}
