<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ExamDutyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ExamTimeController;
use App\Http\Controllers\Auth\PasswordController;

require __DIR__.'/auth.php';

// Redirect root to dashboard (if authenticated, or login if not)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// All Portal Operations Protected by Authentication
Route::middleware('auth')->group(function () {
    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Staff Management Routes
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::get('/create', [StaffController::class, 'create'])->name('create');
        Route::post('/', [StaffController::class, 'store'])->name('store');
        Route::get('/{staff}/edit', [StaffController::class, 'edit'])->name('edit');
        Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
        Route::post('/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])->name('toggle-status');
        Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('destroy');
        
        // Import features (CSV, XLS/XLSX, Textarea Paste)
        Route::get('/upload', [StaffController::class, 'uploadForm'])->name('upload');
        Route::post('/upload-preview', [StaffController::class, 'uploadPreview'])->name('upload-preview');
        Route::post('/paste-preview', [StaffController::class, 'pastePreview'])->name('paste-preview');
        Route::post('/import-commit', [StaffController::class, 'importCommit'])->name('import-commit');
        Route::get('/download-sample', [StaffController::class, 'downloadSample'])->name('download-sample');
        Route::get('/download-sample-excel', [StaffController::class, 'downloadSampleExcel'])->name('download-sample-excel');
    });

    // Exam Duty Routes
    Route::prefix('duty')->name('duty.')->group(function () {
        Route::get('/', [ExamDutyController::class, 'index'])->name('index');
        Route::get('/create', [ExamDutyController::class, 'create'])->name('create');
        Route::post('/store', [ExamDutyController::class, 'store'])->name('store');
        Route::get('/{duty}/edit', [ExamDutyController::class, 'edit'])->name('edit');
        Route::put('/{duty}', [ExamDutyController::class, 'update'])->name('update');
        Route::delete('/{duty}', [ExamDutyController::class, 'destroy'])->name('destroy');
        Route::get('/{duty}/export-pdf', [ExamDutyController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{duty}/export-excel', [ExamDutyController::class, 'exportExcel'])->name('export-excel');
    });

    // Exam Calendar Routes
    Route::get('/exam-calendar', [\App\Http\Controllers\ExamCalendarController::class, 'index'])->name('exam-calendar.index');

    // Reports Module Routes (Annual Duty Hours Matrix & Exports)
    Route::prefix('report')->name('report.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/export-excel', [ReportController::class, 'exportExcel'])->name('export-excel');
        Route::get('/export-pdf', [ReportController::class, 'exportPdf'])->name('export-pdf');
    });

    // Settings / Change Password Routes
    Route::get('/settings/password', [PasswordController::class, 'show'])->name('settings.password');
    Route::put('/settings/password', [PasswordController::class, 'update'])->name('settings.password.update');

    // Configuration (Exam Times etc.)
    Route::prefix('config')->name('config.')->group(function () {
        Route::resource('exam-times', ExamTimeController::class)
            ->only(['index', 'store', 'update', 'destroy']);

        Route::post('exam-times/{exam_time}/toggle-active', [ExamTimeController::class, 'toggleActive'])
            ->name('exam-times.toggle-active');
    });
});
