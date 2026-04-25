<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\PatientController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\ClinicalNoteController;
use App\Http\Controllers\Api\ChartController;
use App\Http\Controllers\Api\PerioChartController;
use App\Http\Controllers\Api\LedgerController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TreatmentPlanController;
use App\Http\Controllers\Api\PrescriptionController;
use App\Http\Controllers\Api\ImagingController;
use App\Http\Controllers\Api\InsuranceController;
use App\Http\Controllers\Api\PreAuthController;
use App\Http\Controllers\Api\InsuranceCarrierController;
use App\Http\Controllers\Api\FeeScheduleController;
use App\Http\Controllers\Api\CdtCodeController;
use Illuminate\Support\Facades\Route;

// Public Auth Routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/imaging/shared/{image}', [ImagingController::class, 'shared'])
    ->middleware('signed')
    ->name('imaging.shared');

// Public presentation route (used in-office on patient-facing display)
Route::get('/treatment-plans/{plan}/present', [TreatmentPlanController::class, 'present']);
Route::patch('/treatment-plans/{plan}/accept', [TreatmentPlanController::class, 'updateStatus']);

// Protected Routes
Route::middleware(['auth:sanctum', 'permission:SCHEDULE_READ'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/mfa/setup', [AuthController::class, 'setupMfa']);
    Route::post('/auth/mfa/verify', [AuthController::class, 'verifyMfa']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // Patients
    Route::apiResource('patients', PatientController::class);
    Route::get('patients/{patient}/timeline', [PatientController::class, 'timeline']);
    Route::post('patients/check-duplicate', [PatientController::class, 'checkDuplicate']);
    Route::post('patients/{patient}/send-portal-invite', [PatientController::class, 'sendPortalInvite']);
    
    // Appointments
    Route::get('appointments', [AppointmentController::class, 'index']);
    Route::post('appointments', [AppointmentController::class, 'store']);
    Route::get('appointments/{appointment}', [AppointmentController::class, 'show']);
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']);
    Route::post('appointments/{appointment}/check-in', [AppointmentController::class, 'checkIn']);
    Route::get('schedule', [AppointmentController::class, 'getSchedule']);
    Route::get('waitlist', [AppointmentController::class, 'waitlistIndex']);
    Route::post('waitlist', [AppointmentController::class, 'waitlistStore']);

    // Clinical
    Route::prefix('clinical')->group(function () {
        Route::get('patients/{patient}/notes', [ClinicalNoteController::class, 'index']);
        Route::post('patients/{patient}/notes', [ClinicalNoteController::class, 'store']);
        Route::get('note-templates', [ClinicalNoteController::class, 'templates']);
        Route::patch('notes/{note}', [ClinicalNoteController::class, 'update']);
        Route::patch('notes/{note}/sign', [ClinicalNoteController::class, 'sign']);
        Route::patch('notes/{note}/lock', [ClinicalNoteController::class, 'lock']);
        Route::post('notes/{note}/amend', [ClinicalNoteController::class, 'amend']);
        
        Route::get('patients/{patient}/chart', [ChartController::class, 'show']);
        Route::post('patients/{patient}/condition', [ChartController::class, 'storeCondition']);
        Route::post('patients/{patient}/restoration', [ChartController::class, 'storeRestoration']);
        
        Route::get('patients/{patient}/perio', [PerioChartController::class, 'index']);
        Route::post('patients/{patient}/perio', [PerioChartController::class, 'store']);
        Route::get('perio/{perioChart}', [PerioChartController::class, 'show']);
        Route::get('perio/{perioChart}/compare/{compareChart}', [PerioChartController::class, 'compare']);
    });

    // Imaging
    Route::prefix('imaging')->group(function () {
        Route::get('patients/{patient}/series', [ImagingController::class, 'index']);
        Route::get('series/{series}', [ImagingController::class, 'showSeries']);
        Route::post('upload', [ImagingController::class, 'upload']);
        Route::get('images/{image}', [ImagingController::class, 'show']);
        Route::patch('images/{image}/annotations', [ImagingController::class, 'updateAnnotations']);
        Route::post('images/{image}/share', [ImagingController::class, 'shareLink']);
    });

    // Billing
    Route::prefix('billing')->group(function () {
        Route::get('patients/{patient}/ledger', [LedgerController::class, 'index']);
        Route::post('payments', [PaymentController::class, 'store']);
    });

    // Treatment Plans
    Route::get('patients/{patient}/treatment-plans', [TreatmentPlanController::class, 'index']);
    Route::post('patients/{patient}/treatment-plans', [TreatmentPlanController::class, 'store']);
    Route::get('treatment-plans/{plan}', [TreatmentPlanController::class, 'show']);
    Route::patch('treatment-plans/{plan}', [TreatmentPlanController::class, 'update']);
    Route::delete('treatment-plans/{plan}', [TreatmentPlanController::class, 'archive']);
    Route::post('treatment-plans/{plan}/procedures', [TreatmentPlanController::class, 'addProcedures']);
    Route::patch('treatment-plans/{plan}/status', [TreatmentPlanController::class, 'updateStatus']);
    Route::post('treatment-plans/{plan}/duplicate', [TreatmentPlanController::class, 'duplicate']);
    Route::post('treatment-plans/{plan}/reorder', [TreatmentPlanController::class, 'reorderProcedures']);

    // CDT Code Search
    Route::get('cdt-codes/search', [CdtCodeController::class, 'search']);

    // Patient Insurance
    Route::get('patients/{patient}/insurance', [InsuranceController::class, 'index']);
    Route::post('patients/{patient}/insurance', [InsuranceController::class, 'store']);
    Route::patch('patients/{patient}/insurance/{insurance}', [InsuranceController::class, 'update']);
    Route::delete('patients/{patient}/insurance/{insurance}', [InsuranceController::class, 'destroy']);
    Route::post('patients/{patient}/insurance/{insurance}/verify', [InsuranceController::class, 'verify']);
    Route::get('patients/{patient}/insurance/{insurance}/eligibility-history', [InsuranceController::class, 'eligibilityHistory']);

    // Pre-Authorizations
    Route::get('patients/{patient}/pre-auths', [PreAuthController::class, 'index']);
    Route::post('patients/{patient}/pre-auths', [PreAuthController::class, 'store']);
    Route::patch('pre-auths/{preAuth}', [PreAuthController::class, 'update']);
    Route::delete('pre-auths/{preAuth}', [PreAuthController::class, 'destroy']);

    // Settings: Insurance Carriers
    Route::get('insurance-carriers', [InsuranceCarrierController::class, 'index']);
    Route::post('insurance-carriers', [InsuranceCarrierController::class, 'store']);
    Route::patch('insurance-carriers/{carrier}', [InsuranceCarrierController::class, 'update']);
    Route::patch('insurance-carriers/{carrier}/deactivate', [InsuranceCarrierController::class, 'deactivate']);

    // Settings: Fee Schedules
    Route::get('fee-schedules', [FeeScheduleController::class, 'index']);
    Route::post('fee-schedules', [FeeScheduleController::class, 'store']);
    Route::patch('fee-schedules/{schedule}', [FeeScheduleController::class, 'update']);
    Route::delete('fee-schedules/{schedule}', [FeeScheduleController::class, 'destroy']);
    Route::get('fee-schedules/{schedule}/items', [FeeScheduleController::class, 'items']);
    Route::post('fee-schedules/{schedule}/items', [FeeScheduleController::class, 'addItem']);
    Route::post('fee-schedules/{schedule}/import-csv', [FeeScheduleController::class, 'importCsv']);
    Route::patch('fee-schedules/{schedule}/bulk-adjust', [FeeScheduleController::class, 'bulkAdjust']);

    // Prescriptions
    Route::get('patients/{patient}/prescriptions', [PrescriptionController::class, 'index']);
    Route::post('patients/{patient}/prescriptions', [PrescriptionController::class, 'store']);
});
