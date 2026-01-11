<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        return response()->json($this->courseService->getAllCourses());
    }

    public function recommended()
    {
        return response()->json($this->courseService->getRecommendedCourses());
    }

    public function popular()
    {
        return response()->json($this->courseService->getPopularCourses());
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'price' => 'numeric',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['instructor_id'] = $request->user()->id;

        $course = $this->courseService->createCourse($data);
        return response()->json($course, 201);
    }

    public function show($id)
    {
        return response()->json($this->courseService->getCourseById($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'string',
            'price' => 'numeric',
        ]);

        $course = $this->courseService->updateCourse($id, $request->all());
        return response()->json($course);
    }

    public function destroy($id)
    {
        $this->courseService->deleteCourse($id);
        return response()->json(null, 204);
    }
}
