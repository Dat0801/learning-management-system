<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;

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
    Route::get('/lessons/{lesson}', [\App\Http\Controllers\Api\LessonController::class, 'show']);
    Route::post('/lessons/{lesson}/complete', [\App\Http\Controllers\Api\LessonController::class, 'complete']);
    Route::delete('/lessons/{lesson}/complete', [\App\Http\Controllers\Api\LessonController::class, 'incomplete']);
});
