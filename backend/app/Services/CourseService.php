<?php

namespace App\Services;

use App\Models\Course;
use App\Notifications\CourseUpdated;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseService
{
    protected $courseRepository;

    public function __construct(CourseRepositoryInterface $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function getAllCourses(array $filters = [])
    {
        return $this->courseRepository->all($filters);
    }

    public function getRecommendedCourses($limit = 3)
    {
        return $this->courseRepository->getRecommended($limit);
    }

    public function getPopularCourses($limit = 3)
    {
        return $this->courseRepository->getPopular($limit);
    }

    public function getCourseById($id)
    {
        return $this->courseRepository->find($id);
    }

    public function createCourse(array $data)
    {
        $course = $this->courseRepository->create($data);

        if (isset($data['lessons']) && is_array($data['lessons'])) {
            foreach ($data['lessons'] as $lessonData) {
                $course->lessons()->create($lessonData);
            }
        }

        return $course;
    }

    public function updateCourse($id, array $data, $user = null)
    {
        $course = $this->courseRepository->find($id);

        if ($user && $user->role === 'instructor' && $course->instructor_id !== $user->id) {
            abort(403, 'You are not allowed to update this course');
        }

        if (isset($data['status']) && $data['status'] === 'published' && $course->status !== 'published') {
            $data['published_at'] = now();
        }

        $updatedCourse = $this->courseRepository->update($id, $data);

        // Notify enrolled students about course updates
        $enrolledUsers = $updatedCourse->enrollments()->with('user')->get()->pluck('user');
        $updates = [];
        if (isset($data['title'])) {
            $updates[] = 'Course title updated';
        }
        if (isset($data['description'])) {
            $updates[] = 'Course description updated';
        }
        if (isset($data['lessons'])) {
            $updates[] = 'New lessons added';
        }

        foreach ($enrolledUsers as $enrolledUser) {
            $enrolledUser->notify(new CourseUpdated($updatedCourse, $updates));
        }

        return $updatedCourse;
    }

    public function deleteCourse($id)
    {
        return $this->courseRepository->delete($id);
    }
}
