<?php

use App\Http\Controllers\StateController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\InstagramController;
use App\Http\Controllers\RazorpayController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminUsersController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// State & Media CMS Endpoints
Route::get('/state', [StateController::class, 'getState']);
Route::post('/state', [StateController::class, 'updateState']);
Route::post('/upload', [StateController::class, 'uploadFile']);

// Social Media Integration
Route::get('/instagram/posts', [InstagramController::class, 'getPosts']);

// Razorpay Order Creation
Route::post('/razorpay/create-order', [RazorpayController::class, 'createOrder'])->middleware('throttle:30,1');

// Public Form Submissions with Rate Limiting (Protection against automated spam & DOS)
Route::middleware('throttle:30,1')->group(function () {
    Route::post('/appointments', [PortalController::class, 'bookAppointmentApi']);
    Route::post('/enquiries', [PortalController::class, 'submitEnquiryApi']);
    Route::post('/subscribers', [PortalController::class, 'subscribeNewsletterApi']);
    Route::post('/registrations', [PortalController::class, 'registerEventApi']);
    Route::post('/eye-donations', [PortalController::class, 'eyeDonationApi']);
    Route::post('/career-applications', [PortalController::class, 'submitCareerApplicationApi']);
    Route::post('/tender-payment', [PortalController::class, 'submitTenderPaymentApi']);
    Route::post('/eye-consultation-enquiries', [PortalController::class, 'submitEyeConsultationEnquiryApi']);
    Route::post('/feedback', [PortalController::class, 'submitPatientFeedbackApi']);
});

// Admin / Data Retrieval Endpoints
Route::get('/eye-donations/{id}/certificate-pdf', [PortalController::class, 'downloadCertificatePdfApi']);
Route::post('/eye-donations/{id}/send-email', [PortalController::class, 'sendCertificateEmailApi'])->middleware('throttle:15,1');

Route::get('/career-applications', [PortalController::class, 'getCareerApplicationsApi']);
Route::delete('/career-applications/{id}', [PortalController::class, 'deleteCareerApplicationApi']);

Route::get('/tender-payments', [PortalController::class, 'getTenderPaymentsApi']);

Route::get('/eye-consultation-enquiries', [PortalController::class, 'getEyeConsultationEnquiriesApi']);
Route::patch('/eye-consultation-enquiries/{id}/status', [PortalController::class, 'updateEyeConsultationEnquiryStatusApi']);
Route::delete('/eye-consultation-enquiries/{id}', [PortalController::class, 'deleteEyeConsultationEnquiryApi']);

Route::get('/feedback', [PortalController::class, 'getPatientFeedbacksApi']);

// Administrator Password Reset Endpoints (Single-use tokens, 30m expiry, Rate limited)
Route::post('/admin/forgot-password', [AdminAuthController::class, 'sendResetLinkApi'])->middleware('throttle:5,15');
Route::post('/admin/reset-password', [AdminAuthController::class, 'resetPasswordApi'])->middleware('throttle:10,15');
Route::get('/admin/verify-reset-token', [AdminAuthController::class, 'verifyTokenApi'])->middleware('throttle:30,1');

// Admin User Management Endpoints (Super Admin Only)
Route::get('/admin/users', [AdminUsersController::class, 'index']);
Route::post('/admin/users', [AdminUsersController::class, 'store']);
Route::put('/admin/users/{id}', [AdminUsersController::class, 'update']);
Route::delete('/admin/users/{id}', [AdminUsersController::class, 'destroy']);
