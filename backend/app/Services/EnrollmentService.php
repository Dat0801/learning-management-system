<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\EnrollmentConfirmation;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use Exception;

class EnrollmentService
{
    protected $enrollmentRepository;

    public function __construct(EnrollmentRepositoryInterface $enrollmentRepository)
    {
        $this->enrollmentRepository = $enrollmentRepository;
    }

    public function enrollUser($userId, $courseId, $transactionId = null)
    {
        // Check if already enrolled
        if ($this->enrollmentRepository->findByCourseAndUser($courseId, $userId)) {
            throw new Exception('User already enrolled in this course.');
        }

        $course = Course::findOrFail($courseId);

        // For paid courses, verify payment transaction
        if ($course->price > 0) {
            if (! $transactionId) {
                throw new Exception('Payment required for this course.');
            }

            $transaction = Transaction::where('transaction_id', $transactionId)
                ->where('user_id', $userId)
                ->where('course_id', $courseId)
                ->first();

            if (! $transaction) {
                throw new Exception('Invalid transaction.');
            }

            if (! $transaction->isCompleted()) {
                throw new Exception('Payment not completed for this transaction.');
            }
        }

        $enrollment = $this->enrollmentRepository->create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'transaction_id' => $transactionId,
            'enrolled_at' => now(),
        ]);

        // Send enrollment confirmation notification
        $user = User::findOrFail($userId);
        $user->notify(new EnrollmentConfirmation($course));

        return $enrollment;
    }

    public function getUserEnrollments($userId)
    {
        return $this->enrollmentRepository->getUserEnrollments($userId);
    }
}
