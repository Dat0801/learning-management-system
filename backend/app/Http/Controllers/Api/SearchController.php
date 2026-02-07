<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function globalSearch(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'type' => 'nullable|in:all,courses,categories,users,lessons',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->input('q');
        $type = $request->input('type', 'all');
        $limit = $request->input('limit', 10);

        $results = [
            'query' => $query,
            'results' => [],
        ];

        if ($type === 'all' || $type === 'courses') {
            $results['results']['courses'] = $this->searchCourses($query, $limit);
        }

        if ($type === 'all' || $type === 'categories') {
            $results['results']['categories'] = $this->searchCategories($query, $limit);
        }

        if ($type === 'all' || $type === 'users') {
            // Only allow user search for authenticated users
            if ($request->user()) {
                $results['results']['users'] = $this->searchUsers($query, $limit);
            }
        }

        if ($type === 'all' || $type === 'lessons') {
            $results['results']['lessons'] = $this->searchLessons($query, $limit);
        }

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    protected function searchCourses(string $query, int $limit)
    {
        $courses = Course::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['instructor', 'category'])
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->limit($limit)
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'price' => $course->price,
                    'thumbnail' => $course->thumbnail,
                    'instructor' => [
                        'id' => $course->instructor->id ?? null,
                        'name' => $course->instructor->name ?? null,
                    ],
                    'category' => [
                        'id' => $course->category->id ?? null,
                        'name' => $course->category->name ?? null,
                        'slug' => $course->category->slug ?? null,
                    ],
                    'enrollments_count' => $course->enrollments_count,
                    'reviews_count' => $course->reviews_count,
                    'average_rating' => round($course->reviews_avg_rating ?? 0, 1),
                ];
            });

        return [
            'count' => $courses->count(),
            'items' => $courses,
        ];
    }

    protected function searchCategories(string $query, int $limit)
    {
        $categories = Category::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%");
        })
            ->withCount('courses')
            ->limit($limit)
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'courses_count' => $category->courses_count,
                ];
            });

        return [
            'count' => $categories->count(),
            'items' => $categories,
        ];
    }

    protected function searchUsers(string $query, int $limit)
    {
        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%");
        })
            ->whereIn('role', ['instructor', 'student'])
            ->select('id', 'name', 'email', 'role')
            ->limit($limit)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ];
            });

        return [
            'count' => $users->count(),
            'items' => $users,
        ];
    }

    protected function searchLessons(string $query, int $limit)
    {
        $lessons = Lesson::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%");
        })
            ->whereHas('course', function ($q) {
                $q->where('status', 'published');
            })
            ->with(['course' => function ($q) {
                $q->select('id', 'title', 'instructor_id');
            }])
            ->limit($limit)
            ->get()
            ->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'content' => substr($lesson->content, 0, 200),
                    'course' => [
                        'id' => $lesson->course->id ?? null,
                        'title' => $lesson->course->title ?? null,
                    ],
                    'duration' => $lesson->duration,
                    'order' => $lesson->order,
                ];
            });

        return [
            'count' => $lessons->count(),
            'items' => $lessons,
        ];
    }

    public function searchCoursesAdvanced(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'instructor_id' => 'nullable|exists:users,id',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'min_rating' => 'nullable|numeric|min:0|max:5',
            'sort' => 'nullable|in:relevance,price_asc,price_desc,rating,popular,newest',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Course::where('status', 'published')
            ->with(['instructor', 'category'])
            ->withCount(['enrollments', 'reviews'])
            ->withAvg('reviews', 'rating');

        // Search query
        if ($request->has('q') && $request->q) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%")
                    ->orWhereHas('instructor', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    })
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        // Category filter
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Instructor filter
        if ($request->has('instructor_id')) {
            $query->where('instructor_id', $request->instructor_id);
        }

        // Price range
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Rating filter
        if ($request->has('min_rating')) {
            $query->havingRaw('AVG(reviews.rating) >= ?', [$request->min_rating]);
        }

        // Sorting
        $sort = $request->input('sort', 'relevance');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderByDesc('reviews_avg_rating');
                break;
            case 'popular':
                $query->orderByDesc('enrollments_count');
                break;
            case 'newest':
                $query->orderByDesc('created_at');
                break;
            default:
                // Relevance - default sorting
                $query->orderByDesc('enrollments_count');
        }

        $perPage = $request->input('per_page', 15);
        $courses = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $courses,
        ]);
    }
}
