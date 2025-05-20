<?php

use App\Http\Controllers\api\ApplicationController;
use App\Http\Controllers\api\ApplicationTemplateController;
use App\Http\Controllers\api\ChangePasswordController;
use App\Http\Controllers\api\CourseController;
use App\Http\Controllers\api\CourseResourceController;
use App\Http\Controllers\api\CourseSessionController;
use App\Http\Controllers\api\EnrollmentController;
use App\Http\Controllers\api\ForgetPasswordController;
use App\Http\Controllers\api\IdCardController;
use App\Http\Controllers\api\LogoutController;
use App\Http\Controllers\api\PasswordResetController;
use App\Http\Controllers\api\PublicationController;
use App\Http\Controllers\api\ResultController;
use App\Http\Controllers\api\ShowNoticeController;
use App\Http\Controllers\api\UserAuthController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('/forget-password', [ForgetPasswordController::class, 'resetPassword']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');
    Route::post('/login', [UserAuthController::class, 'login'])
        ->middleware(['throttle:userLogin']);

    Route::group(['middleware' => 'auth:sanctum'], function(){
        Route::post('/logout', [LogoutController::class, 'logout']);
        Route::get('/user', [UserAuthController::class, 'authUser']);
        Route::post('/change-password', [ChangePasswordController::class, 'changePassword']);
    });

});


// Notice routes
Route::get('show-notice', [ShowNoticeController::class, 'showAll']);
Route::get('show-notice/{id}', [ShowNoticeController::class, 'show']);

// ID Card routes
Route::get('/id-card/verify/{id}', [IdCardController::class, 'verify'])->name('verify');



Route::group(['middleware' => 'auth:sanctum'], function(){
    // Courses routes
    Route::group(['prefix' => 'courses'], function () {
        Route::get('/active/enrollments', [EnrollmentController::class, 'showForStudent']);
        // More specific route should come first
        Route::get('/active/{course_id}/past-sessions', [CourseSessionController::class, 'showPastSessions']);
        Route::get('/active/{courseSession_id}', [CourseSessionController::class, 'showOne']);

        Route::get('/', [CourseController::class, 'showAll']);
        Route::get('/active', [CourseSessionController::class, 'show']);
        Route::get('/{course_id}', [CourseController::class, 'show']);
        Route::get('/active/enrollments/{courseSession_id}', [EnrollmentController::class, 'showForTeacher']);

        // POST routes
        Route::post('/active/enrollments/updateMarks', [EnrollmentController::class, 'updateMarks']);
        Route::post('/active/enrollments/{enrollment}', [EnrollmentController::class, 'update']);
        Route::post('/active/enroll', [EnrollmentController::class, 'store']);
    });

    // Results routes
    Route::group(['prefix' => 'result'], function () {
        Route::get('show/{courseId}', [ResultController::class, 'showResult']);
        Route::get('show-full-result/{year}/{semester}', [ResultController::class, 'showFullResult']);
    });

    // Course Resource routes
    Route::group(['prefix' => 'course-resources'],function () {
        Route::post('/upload', [CourseResourceController::class, 'upload'])->middleware('role:teacher');
        Route::get('/download/{id}', [CourseResourceController::class, 'download'])->middleware('role:teacher|student');
        Route::get('/{course_session_id}', [CourseResourceController::class, 'index']);
        Route::put('/{id}', [CourseResourceController::class, 'update']);
        Route::delete('/{id}', [CourseResourceController::class, 'destroy']);
    });

    // Application Routes (requires auth)
    Route::group(['prefix' => 'applications'], function () {
        Route::get('/templates', [ApplicationTemplateController::class, 'getTemplates']);
        Route::get('/templates/{id}', [ApplicationTemplateController::class, 'showTemplate']);
        Route::post('/submit', [ApplicationController::class, 'submitApplication']);
        Route::get('/my-applications', [ApplicationController::class, 'getMyApplications']);
        Route::get('/pending', [ApplicationController::class, 'getPendingApplications']);
        Route::get('/authorized', [ApplicationController::class, 'authorizedApplications']);
        Route::post('/{id}/authorize', [ApplicationController::class, 'authorizeApplication']);
        Route::get('/{id}/download', [ApplicationController::class, 'downloadAuthorizedCopy']);
        Route::get('/{application}/attachment', [ApplicationController::class, 'downloadAttachment']);

    });

    // Publication routes
    Route::get('/publications', [PublicationController::class, 'showAll']);
    Route::get('/publications/{id}', [PublicationController::class, 'show']);

    // ID Card routes
    Route::get('/id-card', [IdCardController::class, 'generateIdCard']);
});
