<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileDashboardController;
use App\Http\Controllers\Mobile\MobileSpaceController;
use App\Http\Controllers\Mobile\MobileLmsController;
use App\Http\Controllers\Mobile\MobileAbsensiController;
use App\Http\Controllers\Mobile\MobileProfileController;
use App\Http\Controllers\Mobile\MobileStudentController;
use App\Http\Controllers\Mobile\MobileTeacherController;

/*
|--------------------------------------------------------------------------
| Mobile Routes (PWA Interface)
|--------------------------------------------------------------------------
| Prefix: /m  |  Name: mobile.*
*/

Route::prefix('m')->name('mobile.')->group(function () {
    // Guest Routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [MobileAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [MobileAuthController::class, 'login'])->name('login.post');
    });

    // Root /m redirect or dashboard check
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect()->route('mobile.dashboard');
        }
        return redirect()->route('mobile.login');
    });

    Route::get('/pwa-reset', function () {
        return response('<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Cache PWA PembdaHUB</title>
    <style>
        body { font-family: sans-serif; background: #0f172a; color: #ffffff; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; text-align: center; }
        .card { background: #1e293b; padding: 2.5rem; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5); max-width: 400px; border: 2px solid #3b82f6; }
        .spinner { border: 4px solid rgba(255, 255, 255, 0.1); width: 40px; height: 40px; border-radius: 50%; border-left-color: #3b82f6; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="card">
        <div class="spinner"></div>
        <h2 style="font-size: 1.25rem; margin-bottom: 0.5rem;">🔄 Memperbarui Aplikasi Mobile...</h2>
        <p style="font-size: 0.875rem; color: #94a3b8;">Menghapus cache PWA & Service Worker lama di perangkat Anda...</p>
    </div>
    <script>
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for(let registration of registrations) { registration.unregister(); }
            });
        }
        if ("caches" in window) {
            caches.keys().then(function(names) {
                for (let name of names) { caches.delete(name); }
            });
        }
        setTimeout(function() { window.location.href = "/m/dashboard"; }, 1200);
    </script>
</body>
</html>');
    })->name('pwa-reset');

    // Authenticated Mobile Routes
    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [MobileAuthController::class, 'logout'])->name('logout');
        Route::post('/switch-role', [MobileAuthController::class, 'switchRole'])->name('switch-role');

        // Dashboard
        Route::get('/dashboard', [MobileDashboardController::class, 'index'])->name('dashboard');

        // Modul Siswa Existing
        Route::get('/jadwal', [MobileStudentController::class, 'jadwal'])->name('jadwal');
        Route::get('/nilai', [MobileStudentController::class, 'nilai'])->name('nilai');
        Route::get('/tagihan', [MobileStudentController::class, 'tagihan'])->name('tagihan');
        Route::get('/cbt', [MobileStudentController::class, 'cbt'])->name('cbt');
        Route::get('/pkl', [MobileStudentController::class, 'pkl'])->name('pkl');
        Route::post('/pkl/log', [MobileStudentController::class, 'storePklLog'])->name('pkl.log');

        // Modul Guru Existing
        Route::prefix('guru')->name('guru.')->group(function () {
            Route::get('/jadwal', [MobileTeacherController::class, 'jadwal'])->name('jadwal');
            Route::get('/absensi-input', [MobileTeacherController::class, 'absensiInput'])->name('absensi.input');
            Route::get('/absensi-saya', [MobileTeacherController::class, 'absensiSaya'])->name('absensi.saya');
            Route::post('/absensi-store', [MobileTeacherController::class, 'storeAbsensi'])->name('absensi.store');
            Route::get('/tugas', [MobileTeacherController::class, 'tugas'])->name('tugas');
            Route::post('/tugas/{submission}/grade', [MobileTeacherController::class, 'gradeSubmission'])->name('tugas.grade');
            Route::get('/kelas', [MobileTeacherController::class, 'kelas'])->name('kelas');
            Route::get('/edaran', [MobileTeacherController::class, 'edaran'])->name('edaran');
            Route::get('/cbt', [MobileTeacherController::class, 'cbt'])->name('cbt');
            Route::get('/cbt/banks/{bank}', [MobileTeacherController::class, 'cbtBankShow'])->name('cbt.bank.show');
            Route::post('/cbt/exams', [MobileTeacherController::class, 'cbtExamStore'])->name('cbt.exam.store');
            Route::get('/cbt/exams/{exam}/monitor', [MobileTeacherController::class, 'cbtExamMonitor'])->name('cbt.exam.monitor');
            Route::post('/cbt/exams/{exam}/toggle-status', [MobileTeacherController::class, 'cbtExamToggleStatus'])->name('cbt.exam.toggle-status');
            Route::get('/raport', [MobileTeacherController::class, 'raport'])->name('raport');
            Route::get('/hall-of-fame', [MobileTeacherController::class, 'hallOfFame'])->name('hall-of-fame');
        });

        // Pembda Space (Forum)
        Route::prefix('space')->name('space.')->group(function () {
            Route::get('/', [MobileSpaceController::class, 'index'])->name('index');
            Route::get('/create', [MobileSpaceController::class, 'create'])->name('create');
            Route::post('/', [MobileSpaceController::class, 'store'])->name('store');
            Route::get('/{thread}', [MobileSpaceController::class, 'show'])->name('show');
            Route::post('/{thread}/reply', [MobileSpaceController::class, 'reply'])->name('reply');
            Route::post('/{thread}/like', [MobileSpaceController::class, 'like'])->name('like');
        });

        // LMS (Learning Management System)
        Route::prefix('lms')->name('lms.')->group(function () {
            Route::get('/', [MobileLmsController::class, 'index'])->name('index');
            Route::get('/catalog', [MobileLmsController::class, 'catalog'])->name('catalog');
            Route::get('/material/{material}', [MobileLmsController::class, 'material'])->name('material');
            Route::get('/material/{material}/stream', [MobileLmsController::class, 'streamMaterial'])->name('material.stream');
            Route::get('/material/{material}/download', [MobileLmsController::class, 'downloadMaterial'])->name('material.download');
            Route::delete('/material/{material}', [MobileLmsController::class, 'destroyMaterial'])->name('material.destroy');
            Route::post('/assignment/{assignment}/submit', [MobileLmsController::class, 'submitAssignment'])->name('assignment.submit');
            Route::get('/quiz/{quiz}/start', [MobileLmsController::class, 'startQuiz'])->name('quiz.start');
            Route::post('/quiz/attempt/{attempt}/submit', [MobileLmsController::class, 'submitQuiz'])->name('quiz.submit');
            Route::get('/quiz/attempt/{attempt}/result', [MobileLmsController::class, 'quizResult'])->name('quiz.result');
            Route::get('/{course}', [MobileLmsController::class, 'show'])->name('show');

            // Teacher Maker Actions (Course, Module, Material, Assignment, Quiz CRUD)
            Route::post('/course', [MobileLmsController::class, 'storeCourse'])->name('course.store');
            Route::put('/course/{course}', [MobileLmsController::class, 'updateCourse'])->name('course.update');
            Route::delete('/course/{course}', [MobileLmsController::class, 'destroyCourse'])->name('course.destroy');
            Route::get('/course/{course}/delete', [MobileLmsController::class, 'destroyCourse'])->name('course.delete');

            Route::post('/{course}/module', [MobileLmsController::class, 'storeModule'])->name('module.store');
            Route::post('/{course}/material', [MobileLmsController::class, 'storeMaterial'])->name('material.store');
            Route::get('/material/{material}/delete', [MobileLmsController::class, 'destroyMaterial'])->name('material.delete');

            Route::post('/{course}/assignment', [MobileLmsController::class, 'storeAssignment'])->name('assignment.store');
            Route::put('/assignment/{assignment}', [MobileLmsController::class, 'updateAssignment'])->name('assignment.update');
            Route::post('/assignment/{assignment}/update', [MobileLmsController::class, 'updateAssignment'])->name('assignment.update.post');
            Route::delete('/assignment/{assignment}', [MobileLmsController::class, 'destroyAssignment'])->name('assignment.destroy');
            Route::get('/assignment/{assignment}/delete', [MobileLmsController::class, 'destroyAssignment'])->name('assignment.delete');

            Route::post('/{course}/quiz', [MobileLmsController::class, 'storeQuiz'])->name('quiz.store');
            Route::put('/quiz/{quiz}', [MobileLmsController::class, 'updateQuiz'])->name('quiz.update');
            Route::post('/quiz/{quiz}/update', [MobileLmsController::class, 'updateQuiz'])->name('quiz.update.post');
            Route::delete('/quiz/{quiz}', [MobileLmsController::class, 'destroyQuiz'])->name('quiz.destroy');
            Route::get('/quiz/{quiz}/delete', [MobileLmsController::class, 'destroyQuiz'])->name('quiz.delete');
        });

        // Absensi
        Route::prefix('absensi')->name('absensi.')->group(function () {
            Route::get('/', [MobileAbsensiController::class, 'index'])->name('index');
            Route::post('/scan', [MobileAbsensiController::class, 'scan'])->name('scan');
        });

        // Profile
        Route::get('/profile', [MobileProfileController::class, 'index'])->name('profile');
    });
});
