<?php

namespace App\Repositories;

use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;

class CourseRepository implements CourseRepositoryInterface
{
    public function all()
    {
        return Course::with('instructor')
            ->withExists(['enrollments as is_enrolled' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->get();
    }

    public function getRecommended($limit = 3)
    {
        return Course::with('instructor')
            ->withExists(['enrollments as is_enrolled' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function getPopular($limit = 3)
    {
        return Course::with('instructor')
            ->withCount('enrollments')
            ->withExists(['enrollments as is_enrolled' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->orderByDesc('enrollments_count')
            ->limit($limit)
            ->get();
    }

    public function find($id)
    {
        $course = Course::with(['instructor', 'lessons' => function($query) {
            $query->orderBy('order');
        }])->findOrFail($id);

        $userId = auth()->id();

        if ($userId) {
            $course->loadExists(['enrollments as is_enrolled' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }]);

            $completedLessonIds = \App\Models\LessonCompletion::where('user_id', $userId)
                ->whereIn('lesson_id', $course->lessons->pluck('id'))
                ->pluck('lesson_id')
                ->toArray();
                
            foreach ($course->lessons as $lesson) {
                $lesson->is_completed = in_array($lesson->id, $completedLessonIds);
            }
        } else {
            $course->is_enrolled = false;
            foreach ($course->lessons as $lesson) {
                $lesson->is_completed = false;
            }
        }

        return $course;
    }

    public function create(array $data)
    {
        return Course::create($data);
    }

    public function update($id, array $data)
    {
        $course = Course::findOrFail($id);
        $course->update($data);
        return $course;
    }

    public function delete($id)
    {
        return Course::destroy($id);
    }
}
