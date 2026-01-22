<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AdminLessonController;
use App\Http\Controllers\Api\AdminQuizController;
use App\Http\Controllers\Api\ReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Course routes (with optional auth for enrollment status)
Route::middleware('optional.auth')->group(function () {
    Route::get('courses/recommended', [CourseController::class, 'recommended']);
    Route::get('courses/popular', [CourseController::class, 'popular']);
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::get('/lessons/{lesson}', [LessonController::class, 'show']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Profile Management
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/profile/password', [AuthController::class, 'changePassword']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{course}', [WishlistController::class, 'destroy']);

    // Protected Course Operations
    Route::post('courses', [CourseController::class, 'store']);
    Route::put('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);
    
    // Enrollments
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::get('/my-courses', [EnrollmentController::class, 'myEnrollments']);

    // Lessons
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'complete']);
    Route::delete('/lessons/{lesson}/complete', [LessonController::class, 'incomplete']);

    // Quizzes
    Route::get('/lessons/{lesson}/quiz', [QuizController::class, 'showByLesson']);
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);

    // Reviews
    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);
});

// Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [AdminController::class, 'getDashboardStats']);

    // Category Management
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // User Management
    Route::get('/users', [AdminController::class, 'getAllUsers']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::get('/users/{user}', [AdminController::class, 'getUserDetail']);
    Route::put('/users/{user}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);

    // Course Management
    Route::get('/courses', [AdminController::class, 'getAllCoursesAdmin']);
    Route::post('/courses', [AdminController::class, 'createCourseAdmin']);
    Route::get('/courses/{course}', [AdminController::class, 'getCourseDetail']);
    Route::put('/courses/{course}', [AdminController::class, 'updateCourseAdmin']);
    Route::delete('/courses/{course}', [AdminController::class, 'deleteCourseAdmin']);

    // Enrollment Management
    Route::get('/enrollments', [AdminController::class, 'getAllEnrollments']);
    Route::post('/enrollments', [AdminController::class, 'createEnrollment']);
    Route::delete('/enrollments/{enrollment}', [AdminController::class, 'deleteEnrollment']);

    // Lesson Management (Nested under courses)
    Route::get('/courses/{course}/lessons', [AdminLessonController::class, 'index']);
    Route::post('/courses/{course}/lessons', [AdminLessonController::class, 'store']);
    Route::put('/courses/{course}/lessons/{lesson}', [AdminLessonController::class, 'update']);
    Route::delete('/courses/{course}/lessons/{lesson}', [AdminLessonController::class, 'destroy']);

    // Quiz Management
    Route::get('/lessons/{lesson}/quiz', [AdminQuizController::class, 'getQuiz']);
    Route::post('/lessons/{lesson}/quiz', [AdminQuizController::class, 'storeQuiz']);
    Route::delete('/quizzes/{quiz}', [AdminQuizController::class, 'deleteQuiz']);

    Route::post('/quizzes/{quiz}/questions', [AdminQuizController::class, 'storeQuestion']);
    Route::put('/questions/{question}', [AdminQuizController::class, 'updateQuestion']);
    Route::delete('/questions/{question}', [AdminQuizController::class, 'deleteQuestion']);

    Route::post('/questions/{question}/answers', [AdminQuizController::class, 'storeAnswer']);
    Route::put('/answers/{answer}', [AdminQuizController::class, 'updateAnswer']);
    Route::delete('/answers/{answer}', [AdminQuizController::class, 'deleteAnswer']);
});
