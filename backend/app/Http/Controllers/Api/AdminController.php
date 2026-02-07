<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // Dashboard Statistics
    public function getDashboardStats()
    {
        $totalRevenue = Transaction::where('status', 'completed')->sum('amount');
        $pendingRevenue = Transaction::where('status', 'pending')->sum('amount');

        return response()->json([
            'total_users' => User::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'total_courses' => Course::count(),
            'total_enrollments' => Enrollment::count(),
            'total_reviews' => Review::count(),
            'average_rating' => round(Review::avg('rating') ?? 0, 1),
            'revenue' => $totalRevenue,
            'pending_revenue' => $pendingRevenue,
            'total_transactions' => Transaction::count(),
            'completed_transactions' => Transaction::where('status', 'completed')->count(),
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
            'email' => 'email|unique:users,email,'.$userId,
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
        if (! in_array($instructor->role, ['instructor', 'admin'])) {
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

    // Transaction Management
    public function getAllTransactions(Request $request)
    {
        $query = Transaction::with(['user', 'course']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('transaction_id', 'like', "%{$search}%")
                ->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('course', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");
                });
        }

        $transactions = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($transactions);
    }

    public function getTransactionDetail($transactionId)
    {
        $transaction = Transaction::with(['user', 'course'])
            ->findOrFail($transactionId);

        return response()->json($transaction);
    }

    // Revenue Management
    public function getRevenueStats(Request $request)
    {
        $query = Transaction::where('status', 'completed');

        // Date range filter
        if ($request->has('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }

        $totalRevenue = (clone $query)->sum('amount');
        $totalTransactions = (clone $query)->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Revenue by payment method
        $revenueByMethod = (clone $query)
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        // Revenue by date (for chart)
        $revenueByDate = (clone $query)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as revenue, COUNT(*) as transactions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top courses by revenue
        $topCourses = (clone $query)
            ->selectRaw('course_id, SUM(amount) as revenue, COUNT(*) as transactions')
            ->with('course:id,title,instructor_id')
            ->groupBy('course_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        // Revenue by instructor
        $revenueByInstructor = Transaction::where('status', 'completed')
            ->whereHas('course', function ($q) {
                $q->whereNotNull('instructor_id');
            })
            ->join('courses', 'transactions.course_id', '=', 'courses.id')
            ->join('users', 'courses.instructor_id', '=', 'users.id')
            ->selectRaw('users.id, users.name, SUM(transactions.amount) as revenue, COUNT(transactions.id) as transactions')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('revenue')
            ->get();

        // Monthly comparison
        $currentMonth = (clone $query)
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('amount');

        $lastMonth = Transaction::where('status', 'completed')
            ->whereMonth('paid_at', now()->subMonth()->month)
            ->whereYear('paid_at', now()->subMonth()->year)
            ->sum('amount');

        $monthlyGrowth = $lastMonth > 0
            ? round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2)
            : 0;

        return response()->json([
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'average_transaction' => round($averageTransaction, 2),
            'current_month_revenue' => $currentMonth,
            'last_month_revenue' => $lastMonth,
            'monthly_growth_percentage' => $monthlyGrowth,
            'revenue_by_method' => $revenueByMethod,
            'revenue_by_date' => $revenueByDate,
            'top_courses' => $topCourses,
            'revenue_by_instructor' => $revenueByInstructor,
        ]);
    }

    public function getRevenueByCourse(Request $request)
    {
        $query = Transaction::where('status', 'completed')
            ->with('course.instructor')
            ->selectRaw('course_id, SUM(amount) as revenue, COUNT(*) as transactions')
            ->groupBy('course_id');

        if ($request->has('start_date')) {
            $query->whereDate('paid_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('paid_at', '<=', $request->end_date);
        }

        $results = $query->orderByDesc('revenue')
            ->paginate($request->get('per_page', 15));

        return response()->json($results);
    }

    public function getRevenueByInstructor(Request $request)
    {
        $query = Transaction::where('status', 'completed')
            ->whereHas('course', function ($q) {
                $q->whereNotNull('instructor_id');
            })
            ->join('courses', 'transactions.course_id', '=', 'courses.id')
            ->join('users', 'courses.instructor_id', '=', 'users.id')
            ->selectRaw('users.id, users.name, users.email, SUM(transactions.amount) as revenue, COUNT(transactions.id) as transactions')
            ->groupBy('users.id', 'users.name', 'users.email');

        if ($request->has('start_date')) {
            $query->whereDate('transactions.paid_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('transactions.paid_at', '<=', $request->end_date);
        }

        $results = $query->orderByDesc('revenue')
            ->paginate($request->get('per_page', 15));

        return response()->json($results);
    }

    public function getRevenueReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'nullable|in:json,csv',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $transactions = Transaction::where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->with(['user', 'course.instructor'])
            ->orderBy('paid_at', 'desc')
            ->get();

        $summary = [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'total_revenue' => $transactions->sum('amount'),
            'total_transactions' => $transactions->count(),
            'average_transaction' => $transactions->count() > 0
                ? round($transactions->sum('amount') / $transactions->count(), 2)
                : 0,
            'revenue_by_method' => $transactions->groupBy('payment_method')
                ->map(function ($group) {
                    return [
                        'revenue' => $group->sum('amount'),
                        'count' => $group->count(),
                    ];
                }),
        ];

        if ($request->input('format') === 'csv') {
            // In a real implementation, you would generate CSV here
            return response()->json([
                'message' => 'CSV export not yet implemented',
                'data' => $transactions,
            ]);
        }

        return response()->json([
            'summary' => $summary,
            'transactions' => $transactions,
        ]);
    }
}
