<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AdminLessonController;
use App\Http\Controllers\Api\AdminLessonResourceController;
use App\Http\Controllers\Api\AdminQuizController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CertificateController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\FileUploadController;
use App\Http\Controllers\Api\InstructorController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\LessonNoteController;
use App\Http\Controllers\Api\LessonQuestionController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\QuizController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');

// Search routes (public)
Route::get('/search', [SearchController::class, 'globalSearch']);
Route::get('/search/courses', [SearchController::class, 'searchCoursesAdvanced']);

// Public Course routes (with optional auth for enrollment status)
Route::middleware('optional.auth')->group(function () {
    Route::get('courses/recommended', [CourseController::class, 'recommended']);
    Route::get('courses/popular', [CourseController::class, 'popular']);
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/{course}', [CourseController::class, 'show']);
    Route::get('courses/{course}/reviews', [ReviewController::class, 'index']);
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

    // Email Verification
    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail']);

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{course}', [WishlistController::class, 'destroy']);

    // Protected Course Operations
    Route::post('courses', [CourseController::class, 'store']);
    Route::put('courses/{course}', [CourseController::class, 'update']);
    Route::delete('courses/{course}', [CourseController::class, 'destroy']);

    // Payments
    Route::post('/courses/{course}/payment-intent', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/payments/confirm', [PaymentController::class, 'confirmPayment']);
    Route::get('/my-transactions', [PaymentController::class, 'myTransactions']);

    // Enrollments
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store']);
    Route::get('/my-courses', [EnrollmentController::class, 'myEnrollments']);

    // Lessons
    Route::post('/lessons/{lesson}/complete', [LessonController::class, 'complete']);
    Route::delete('/lessons/{lesson}/complete', [LessonController::class, 'incomplete']);

    // Lesson Notes
    Route::get('/lessons/{lesson}/note', [LessonNoteController::class, 'show']);
    Route::post('/lessons/{lesson}/note', [LessonNoteController::class, 'store']);

    // Lesson Q&A
    Route::get('/lessons/{lesson}/questions', [LessonQuestionController::class, 'index']);
    Route::post('/lessons/{lesson}/questions', [LessonQuestionController::class, 'store']);
    Route::post('/questions/{question}/answers', [LessonQuestionController::class, 'answer']);

    // Quizzes
    Route::get('/lessons/{lesson}/quiz', [QuizController::class, 'showByLesson']);
    Route::post('/quizzes/{quiz}/submit', [QuizController::class, 'submit']);

    // Reviews
    Route::post('/courses/{course}/reviews', [ReviewController::class, 'store']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    // Certificates
    Route::get('/courses/{course}/certificate', [CertificateController::class, 'checkCertificate']);
    Route::get('/my-certificates', [CertificateController::class, 'myCertificates']);
    Route::get('/certificates/{certificateNumber}/download', [CertificateController::class, 'downloadCertificate']);

    // Coupons
    Route::post('/coupons/validate', [CouponController::class, 'validateCoupon']);

    // File Upload Routes
    Route::post('/upload/image', [FileUploadController::class, 'uploadImage']);
    Route::post('/upload/video', [FileUploadController::class, 'uploadVideo']);
    Route::post('/upload/document', [FileUploadController::class, 'uploadDocument']);
    Route::post('/upload/resource', [FileUploadController::class, 'uploadResource']);
    Route::delete('/upload/delete', [FileUploadController::class, 'deleteFile']);

    // Instructor Routes
    Route::middleware(['auth:sanctum', 'instructor'])->prefix('instructor')->group(function () {
        Route::get('/dashboard/stats', [InstructorController::class, 'getDashboardStats']);
        Route::get('/courses', [InstructorController::class, 'getMyCourses']);
        Route::get('/courses/{course}/analytics', [InstructorController::class, 'getCourseAnalytics']);
        Route::get('/revenue', [InstructorController::class, 'getRevenue']);
        Route::get('/students', [InstructorController::class, 'getStudents']);
    });
});

// Public certificate verification
Route::get('/certificates/{certificateNumber}/verify', [CertificateController::class, 'verifyCertificate']);

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

    // Lesson Resources Management
    Route::get('/lessons/{lesson}/resources', [AdminLessonResourceController::class, 'index']);
    Route::post('/lessons/{lesson}/resources', [AdminLessonResourceController::class, 'store']);
    Route::delete('/resources/{resource}', [AdminLessonResourceController::class, 'destroy']);

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

    // Transaction Management
    Route::get('/transactions', [AdminController::class, 'getAllTransactions']);
    Route::get('/transactions/{transaction}', [AdminController::class, 'getTransactionDetail']);

    // Revenue Management
    Route::get('/revenue/stats', [AdminController::class, 'getRevenueStats']);
    Route::get('/revenue/by-course', [AdminController::class, 'getRevenueByCourse']);
    Route::get('/revenue/by-instructor', [AdminController::class, 'getRevenueByInstructor']);
    Route::get('/revenue/report', [AdminController::class, 'getRevenueReport']);

    // Coupon Management
    Route::get('/coupons', [CouponController::class, 'index']);
    Route::post('/coupons', [CouponController::class, 'store']);
    Route::get('/coupons/{coupon}', [CouponController::class, 'show']);
    Route::put('/coupons/{coupon}', [CouponController::class, 'update']);
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy']);
});
