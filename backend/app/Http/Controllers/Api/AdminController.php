<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Dashboard Statistics
    public function getDashboardStats()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::count(),
            'total_reviews' => Review::count(),
            'average_rating' => round(Review::avg('rating') ?? 0, 1),
            'revenue' => Course::sum('price'),
        ]);
    }

    // User Management
    public function getAllUsers(Request $request)
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->paginate($request->get('per_page', 15));
        return response()->json($users);
    }

    public function getUserDetail($userId)
    {
        $user = User::with(['courses', 'enrollments'])->findOrFail($userId);
        return response()->json($user);
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:student,instructor,admin',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return response()->json($user, 201);
    }

    public function updateUser(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|unique:users,email,' . $userId,
            'role' => 'in:student,instructor,admin',
            'password' => 'nullable|string|min:8',
        ]);

        $data = $request->all();
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        return response()->json($user);
    }

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);
        $user->delete();
        return response()->json(['message' => 'User deleted successfully'], 204);
    }

    // Course Management
    public function getAllCoursesAdmin(Request $request)
    {
        $query = Course::with(['instructor', 'category']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $courses = $query->paginate($request->get('per_page', 15));
        return response()->json($courses);
    }

    public function getCourseDetail($courseId)
    {
        $course = Course::with(['instructor', 'category', 'lessons', 'enrollments'])->findOrFail($courseId);
        return response()->json($course);
    }

    public function createCourseAdmin(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'instructor_id' => 'required|exists:users,id',
            'status' => 'in:draft,published,archived',
        ]);

        // Verify that the assigned user is actually an instructor or admin
        $instructor = User::findOrFail($request->instructor_id);
        if (!in_array($instructor->role, ['instructor', 'admin'])) {
            return response()->json(['message' => 'Selected user must be an instructor or admin'], 422);
        }

        $course = Course::create([
            'title' => $request->title,
            'description' => $request->description,
            'price' => $request->price,
            'instructor_id' => $request->instructor_id,
            'status' => $request->status ?? 'draft',
        ]);

        return response()->json($course, 201);
    }

    public function updateCourseAdmin(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);

        $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'price' => 'numeric|min:0',
            'status' => 'in:draft,published,archived',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $course->update($request->all());
        return response()->json($course);
    }

    public function deleteCourseAdmin($courseId)
    {
        $course = Course::findOrFail($courseId);
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully'], 204);
    }

    // Enrollment Management
    public function getAllEnrollments(Request $request)
    {
        $query = Enrollment::with(['user', 'course']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->paginate($request->get('per_page', 15));
        return response()->json($enrollments);
    }

    public function createEnrollment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
        ]);

        // Check if already enrolled
        $exists = Enrollment::where('user_id', $request->user_id)
            ->where('course_id', $request->course_id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'User is already enrolled in this course'], 422);
        }

        $enrollment = Enrollment::create([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'enrolled_at' => now(),
        ]);

        return response()->json($enrollment, 201);
    }

    public function deleteEnrollment($enrollmentId)
    {
        $enrollment = Enrollment::findOrFail($enrollmentId);
        $enrollment->delete();
        return response()->json(['message' => 'Enrollment deleted successfully'], 204);
    }
}
