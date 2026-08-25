<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Orang Tua (Parent) Routes
|--------------------------------------------------------------------------
| Routes for parents.
| Prefix: /orang-tua   |  Name: orangtua.*
| Middleware: auth, role:orang_tua
*/

Route::prefix('orang-tua')->name('orangtua.')->middleware('auth', 'role:orang_tua')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\OrangTua\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/anak/{student}/nilai', [App\Http\Controllers\OrangTua\DashboardController::class, 'nilai'])->name('anak.nilai');
    Route::get('/anak/{student}/raport/{reportCard}/download', [App\Http\Controllers\OrangTua\DashboardController::class, 'downloadRaport'])->name('anak.raport.download');
    Route::get('/anak/{student}/tagihan', [App\Http\Controllers\OrangTua\DashboardController::class, 'tagihan'])->name('anak.tagihan');
    Route::get('/anak/{student}/absensi', [App\Http\Controllers\OrangTua\DashboardController::class, 'absensi'])->name('anak.absensi');
    Route::get('/anak/{student}/jadwal', [App\Http\Controllers\OrangTua\DashboardController::class, 'jadwal'])->name('anak.jadwal');
    Route::get('/anak/{student}/konseling', [App\Http\Controllers\OrangTua\DashboardController::class, 'konseling'])->name('anak.konseling');
    Route::get('/anak/{student}/dna', [App\Http\Controllers\OrangTua\ParentStudentDnaController::class, 'show'])->name('anak.dna');
    Route::get('/anak/{student}/dna/pdf', [App\Http\Controllers\OrangTua\ParentStudentDnaController::class, 'printPdf'])->name('anak.dna.pdf');
});
