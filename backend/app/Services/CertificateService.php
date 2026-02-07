<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use App\Models\User;
use App\Notifications\CourseCompleted;
use Exception;
use Illuminate\Support\Str;

class CertificateService
{
    public function checkAndGenerateCertificate(User $user, Course $course): ?Certificate
    {
        // Check if user is enrolled
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if (! $enrollment) {
            throw new Exception('User is not enrolled in this course.');
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingCertificate) {
            return $existingCertificate;
        }

        // Check if course is completed
        if (! $this->isCourseCompleted($user, $course)) {
            return null;
        }

        // Generate certificate
        return $this->generateCertificate($user, $course);
    }

    public function isCourseCompleted(User $user, Course $course): bool
    {
        $totalLessons = $course->lessons()->count();

        if ($totalLessons === 0) {
            return false;
        }

        $completedLessons = LessonCompletion::where('user_id', $user->id)
            ->whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->count();

        return $completedLessons === $totalLessons;
    }

    public function generateCertificate(User $user, Course $course): Certificate
    {
        $certificateNumber = 'CERT-'.strtoupper(Str::random(12)).'-'.now()->format('Ymd');

        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_number' => $certificateNumber,
            'issued_date' => now(),
            'certificate_url' => $this->generateCertificateUrl($certificateNumber),
        ]);

        // Send course completion notification
        $user->notify(new CourseCompleted($course, $certificate));

        return $certificate;
    }

    protected function generateCertificateUrl(string $certificateNumber): string
    {
        // In a real implementation, this would generate a PDF or image certificate
        // For now, we'll return a URL that can be used to view/download the certificate
        return url('/certificates/'.$certificateNumber);
    }

    public function getUserCertificates(User $user)
    {
        return Certificate::where('user_id', $user->id)
            ->with('course')
            ->orderBy('issued_date', 'desc')
            ->get();
    }

    public function getCertificateByNumber(string $certificateNumber): Certificate
    {
        return Certificate::where('certificate_number', $certificateNumber)
            ->with(['user', 'course.instructor'])
            ->firstOrFail();
    }

    public function verifyCertificate(string $certificateNumber): bool
    {
        return Certificate::where('certificate_number', $certificateNumber)->exists();
    }
}
