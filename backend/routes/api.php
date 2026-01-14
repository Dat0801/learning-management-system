<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\LessonController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public Course routes (with optional auth for enrollment status)
Route::middleware('optional.auth')->group(function () {
    Route::get('courses/recommended', [CourseController::class, 'recommended']);
    Route::get('courses/popular', [CourseController::class, 'popular']);
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Protected Course Operations
    Route::post('courses', [CourseController::class, 'store']);
    Route::put('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);
    
    // Enrollments
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::get('/my-courses', [EnrollmentController::class, 'myEnrollments']);

    // Lessons
    Route::get('/lessons/{lesson}', [LessonController::class, 'show']);
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'complete']);
    Route::delete('/lessons/{lesson}/complete', [LessonController::class, 'incomplete']);

    // Quizzes
    Route::get('/lessons/{lesson}/quiz', [QuizController::class, 'showByLesson']);
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);
});

// Admin Routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard/stats', [AdminController::class, 'getDashboardStats']);

    // User Management
    Route::get('/users', [AdminController::class, 'getAllUsers']);
    Route::get('/users/{user}', [AdminController::class, 'getUserDetail']);
    Route::put('/users/{user}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser']);

    // Course Management
    Route::get('/courses', [AdminController::class, 'getAllCoursesAdmin']);
    Route::get('/courses/{course}', [AdminController::class, 'getCourseDetail']);
    Route::put('/courses/{course}', [AdminController::class, 'updateCourseAdmin']);
    Route::delete('/courses/{course}', [AdminController::class, 'deleteCourseAdmin']);

    // Enrollment Management
    Route::get('/enrollments', [AdminController::class, 'getAllEnrollments']);
    Route::delete('/enrollments/{enrollment}', [AdminController::class, 'deleteEnrollment']);
});
