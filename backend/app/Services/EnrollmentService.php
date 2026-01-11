<?php

namespace App\Services;

use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use Exception;

class EnrollmentService
{
    protected $enrollmentRepository;

    public function __construct(EnrollmentRepositoryInterface $enrollmentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
    }

    public function enrollUser($userId, $courseId)
    {
        // Check if already enrolled
        if ($this->enrollmentRepository->findByCourseAndUser($courseId, $userId)) {
            throw new Exception("User already enrolled in this course.");
        }

        return $this->enrollmentRepository->create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'enrolled_at' => now(),
        ]);
    }

    public function getUserEnrollments($userId)
    {
        return $this->enrollmentRepository->getUserEnrollments($userId);
    }
}
