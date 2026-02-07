<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LessonCompletion;
use App\Models\Review;
use App\Models\Transaction;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function getDashboardStats(Request $request)
    {
        $instructor = $request->user();

        // Get instructor's courses
        $courses = Course::where('instructor_id', $instructor->id)->get();
        $courseIds = $courses->pluck('id');

        // Calculate statistics
        $totalCourses = $courses->count();
        $publishedCourses = $courses->where('status', 'published')->count();
        $draftCourses = $courses->where('status', 'draft')->count();

        // Enrollment statistics
        $totalEnrollments = Enrollment::whereIn('course_id', $courseIds)->count();
        $totalStudents = Enrollment::whereIn('course_id', $courseIds)
            ->distinct('user_id')
            ->count('user_id');

        // Revenue statistics
        $totalRevenue = Transaction::whereIn('course_id', $courseIds)
            ->where('status', 'completed')
            ->sum('amount');

        $monthlyRevenue = Transaction::whereIn('course_id', $courseIds)
            ->where('status', 'completed')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        // Review statistics
        $totalReviews = Review::whereIn('course_id', $courseIds)->count();
        $averageRating = Review::whereIn('course_id', $courseIds)->avg('rating') ?? 0;

        // Course completion statistics
        $totalLessons = $courses->sum(function ($course) {
            return $course->lessons()->count();
        });

        $completedLessons = LessonCompletion::whereIn('lesson_id', function ($query) use ($courseIds) {
            $query->select('id')
                ->from('lessons')
                ->whereIn('course_id', $courseIds);
        })->count();

        // Recent enrollments
        $recentEnrollments = Enrollment::whereIn('course_id', $courseIds)
            ->with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top performing courses
        $topCourses = Course::where('instructor_id', $instructor->id)
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'total_courses' => $totalCourses,
            'published_courses' => $publishedCourses,
            'draft_courses' => $draftCourses,
            'total_enrollments' => $totalEnrollments,
            'total_students' => $totalStudents,
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'total_reviews' => $totalReviews,
            'average_rating' => round($averageRating, 1),
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'recent_enrollments' => $recentEnrollments,
            'top_courses' => $topCourses,
        ]);
    }

    public function getMyCourses(Request $request)
    {
        $instructor = $request->user();

        $query = Course::where('instructor_id', $instructor->id)
            ->with(['category', 'enrollments', 'reviews']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $courses = $query->withCount(['enrollments', 'lessons', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($courses);
    }

    public function getCourseAnalytics(Request $request, $courseId)
    {
        $instructor = $request->user();
        $course = Course::where('instructor_id', $instructor->id)
            ->findOrFail($courseId);

        // Enrollment statistics
        $totalEnrollments = $course->enrollments()->count();
        $recentEnrollments = $course->enrollments()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        // Revenue statistics
        $totalRevenue = Transaction::where('course_id', $course->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Completion statistics
        $totalLessons = $course->lessons()->count();
        $completedLessons = LessonCompletion::whereIn('lesson_id', $course->lessons()->pluck('id'))
            ->distinct('user_id')
            ->count('user_id');

        $completionRate = $totalEnrollments > 0
            ? round(($completedLessons / $totalEnrollments) * 100, 2)
            : 0;

        // Review statistics
        $totalReviews = $course->reviews()->count();
        $averageRating = $course->reviews()->avg('rating') ?? 0;

        // Enrollment over time (last 30 days)
        $enrollmentsOverTime = Enrollment::where('course_id', $course->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Student progress
        $studentProgress = Enrollment::where('course_id', $course->id)
            ->with(['user' => function ($query) {
                $query->select('id', 'name', 'email');
            }])
            ->withCount(['user' => function ($query) use ($course) {
                $query->selectRaw('COUNT(*)')
                    ->from('lesson_completions')
                    ->whereIn('lesson_id', $course->lessons()->pluck('id'));
            }])
            ->limit(20)
            ->get();

        return response()->json([
            'course' => $course->load(['category', 'instructor']),
            'enrollments' => [
                'total' => $totalEnrollments,
                'recent' => $recentEnrollments,
            ],
            'revenue' => [
                'total' => $totalRevenue,
            ],
            'completion' => [
                'total_lessons' => $totalLessons,
                'completed_students' => $completedLessons,
                'completion_rate' => $completionRate,
            ],
            'reviews' => [
                'total' => $totalReviews,
                'average_rating' => round($averageRating, 1),
            ],
            'enrollments_over_time' => $enrollmentsOverTime,
            'student_progress' => $studentProgress,
        ]);
    }

    public function getRevenue(Request $request)
    {
        $instructor = $request->user();
        $courseIds = Course::where('instructor_id', $instructor->id)->pluck('id');

        $query = Transaction::whereIn('course_id', $courseIds)
            ->where('status', 'completed')
            ->with('course');

        if ($request->has('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('paid_at', 'desc')
            ->paginate($request->get('per_page', 15));

        $totalRevenue = Transaction::whereIn('course_id', $courseIds)
            ->where('status', 'completed')
            ->sum('amount');

        return response()->json([
            'transactions' => $transactions,
            'total_revenue' => $totalRevenue,
        ]);
    }

    public function getStudents(Request $request)
    {
        $instructor = $request->user();
        $courseIds = Course::where('instructor_id', $instructor->id)->pluck('id');

        $query = Enrollment::whereIn('course_id', $courseIds)
            ->with(['user', 'course']);

        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        $enrollments = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($enrollments);
    }
}
