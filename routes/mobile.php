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

            Route::post('/{course}/module', [MobileLmsController::class, 'storeModule'])->name('module.store');
            Route::post('/{course}/material', [MobileLmsController::class, 'storeMaterial'])->name('material.store');
            Route::post('/{course}/assignment', [MobileLmsController::class, 'storeAssignment'])->name('assignment.store');
            Route::delete('/assignment/{assignment}', [MobileLmsController::class, 'destroyAssignment'])->name('assignment.destroy');
            Route::post('/{course}/quiz', [MobileLmsController::class, 'storeQuiz'])->name('quiz.store');
            Route::delete('/quiz/{quiz}', [MobileLmsController::class, 'destroyQuiz'])->name('quiz.destroy');
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
