<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\StoreCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    public function index()
    {
        $courses = $this->courseService->getAllCourses();
        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses),
        ]);
    }

    public function recommended()
    {
        $courses = $this->courseService->getRecommendedCourses();
        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses),
        ]);
    }

    public function popular()
    {
        $courses = $this->courseService->getPopularCourses();
        return response()->json([
            'success' => true,
            'data' => CourseResource::collection($courses),
        ]);
    }

    public function store(StoreCourseRequest $request)
    {
        $data = $request->all();
        $data['instructor_id'] = $request->user()->id;

        $course = $this->courseService->createCourse($data);
        return response()->json([
            'success' => true,
            'message' => 'Course created',
            'data' => new CourseResource($course),
        ], 201);
    }

    public function show($id)
    {
        $course = $this->courseService->getCourseById($id);
        return response()->json([
            'success' => true,
            'data' => new CourseResource($course),
        ]);
    }

    public function update(UpdateCourseRequest $request, $id)
    {
        $course = $this->courseService->updateCourse($id, $request->all(), $request->user());
        return response()->json([
            'success' => true,
            'message' => 'Course updated',
            'data' => new CourseResource($course),
        ]);
    }

    public function destroy($id)
    {
        $this->courseService->deleteCourse($id);
        return response()->json([
            'success' => true,
            'message' => 'Course deleted',
        ], 204);
    }
}
