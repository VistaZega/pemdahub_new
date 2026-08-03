<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Treasurer (Bendahara) Routes
|--------------------------------------------------------------------------
| Routes for school treasurer.
| Prefix: /bendahara   |  Name: treasurer.*
| Middleware: auth, treasurer
*/

Route::prefix('bendahara')->name('treasurer.')->middleware('auth', 'treasurer')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Treasurer\DashboardController::class, 'index'])->name('dashboard');
    
    // Student Bills Management
    Route::get('bills/bulk-create', [App\Http\Controllers\Treasurer\StudentBillController::class, 'bulkCreate'])->name('bills.bulk-create');
    Route::post('bills/bulk-store', [App\Http\Controllers\Treasurer\StudentBillController::class, 'bulkStore'])->name('bills.bulk-store');
    Route::post('bills/bulk-update-amount', [App\Http\Controllers\Treasurer\StudentBillController::class, 'bulkUpdateAmount'])->name('bills.bulk-update-amount');
    Route::post('bills/bulk-delete', [App\Http\Controllers\Treasurer\StudentBillController::class, 'bulkDelete'])->name('bills.bulk-delete');
    Route::get('bills/export', [App\Http\Controllers\Treasurer\StudentBillController::class, 'export'])->name('bills.export');
    Route::resource('bills', App\Http\Controllers\Treasurer\StudentBillController::class);
    
    // Payments Management
    Route::get('payments/export', [App\Http\Controllers\Treasurer\PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/bulk-create', [App\Http\Controllers\Treasurer\PaymentController::class, 'bulkCreate'])->name('payments.bulk-create');
    Route::get('payments/fetch-bills', [App\Http\Controllers\Treasurer\PaymentController::class, 'fetchBills'])->name('payments.fetch-bills');
    Route::post('payments/bulk-store', [App\Http\Controllers\Treasurer\PaymentController::class, 'bulkStore'])->name('payments.bulk-store');
    Route::post('payments/batch-store', [App\Http\Controllers\Treasurer\PaymentController::class, 'batchStore'])->name('payments.batch-store');
    Route::get('payments/{payment}/receipt', [App\Http\Controllers\Treasurer\PaymentController::class, 'downloadReceipt'])->name('payments.receipt');
    Route::resource('payments', App\Http\Controllers\Treasurer\PaymentController::class);
    
    // Reports
    Route::get('reports', [App\Http\Controllers\Treasurer\ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [App\Http\Controllers\Treasurer\ReportController::class, 'export'])->name('reports.export');
    
    // Yayasan Consolidation Report
    Route::get('konsolidasi-yayasan', [App\Http\Controllers\Treasurer\ConsolidationReportController::class, 'index'])->name('consolidation.index');
    
    // Kepegawaian & Penugasan Jabatan (Read-Only)
    Route::get('assignments/positions', [App\Http\Controllers\Treasurer\PositionAssignmentController::class, 'index'])->name('assignments.positions.index');
    Route::get('salary-report', [App\Http\Controllers\Treasurer\WorkloadSummaryController::class, 'salaryReport'])->name('salary-report');
    Route::get('salary-report/export', [App\Http\Controllers\Treasurer\WorkloadSummaryController::class, 'exportSalaryReport'])->name('salary-report.export');
    Route::get('workload/{employee}/salary-detail', [App\Http\Controllers\Treasurer\WorkloadSummaryController::class, 'salaryDetail'])->name('salary-detail');
    Route::get('workload/{employee}/salary-slip', [App\Http\Controllers\Treasurer\WorkloadSummaryController::class, 'salarySlip'])->name('salary-slip');
    Route::get('workload/{employee}/salary-slip-pdf', [App\Http\Controllers\Treasurer\WorkloadSummaryController::class, 'salarySlipPdf'])->name('salary-slip-pdf');
    
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/slip-search', [App\Http\Controllers\Treasurer\PayrollController::class, 'slipSearch'])->name('slip-search');
        Route::get('/settings', [App\Http\Controllers\Treasurer\PayrollController::class, 'settings'])->name('settings');
    });
});
