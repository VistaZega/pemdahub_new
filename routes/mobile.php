<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\MobileDashboardController;
use App\Http\Controllers\Mobile\MobileSpaceController;
use App\Http\Controllers\Mobile\MobileLmsController;
use App\Http\Controllers\Mobile\MobileAbsensiController;
use App\Http\Controllers\Mobile\MobileProfileController;

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

        // Dashboard
        Route::get('/dashboard', [MobileDashboardController::class, 'index'])->name('dashboard');

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
            Route::get('/{course}', [MobileLmsController::class, 'show'])->name('show');
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
