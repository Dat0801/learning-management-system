<?php

namespace App\Services;

use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseService
{
    protected $courseRepository;

    public function __construct(CourseRepositoryInterface $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }

    public function getAllCourses()
    {
        return $this->courseRepository->all();
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
        return $this->courseRepository->create($data);
    }

    public function updateCourse($id, array $data)
    {
        return $this->courseRepository->update($id, $data);
    }

    public function deleteCourse($id)
    {
        return $this->courseRepository->delete($id);
    }
}
