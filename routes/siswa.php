<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Siswa Routes
|--------------------------------------------------------------------------
| Routes for students (Siswa).
| Prefix: /siswa   |  Name: siswa.*
| Middleware: auth, role:siswa
*/

Route::prefix('siswa')->name('siswa.')->middleware('auth', 'role:siswa')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Siswa\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [App\Http\Controllers\Siswa\EducationalCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/jadwal', [App\Http\Controllers\Siswa\DashboardController::class, 'jadwal'])->name('jadwal');
    Route::get('/nilai', [App\Http\Controllers\Siswa\DashboardController::class, 'nilai'])->name('nilai');
    Route::get('/tagihan', [App\Http\Controllers\Siswa\DashboardController::class, 'tagihan'])->name('tagihan');
    Route::get('/absensi', [App\Http\Controllers\Siswa\DashboardController::class, 'absensi'])->name('absensi')->middleware('feature:siswa_view_attendance_recap');
    Route::post('/attendance/gps-scan', [App\Http\Controllers\Api\AttendanceController::class, 'handleGpsScan'])->name('attendance.gps-scan');
    Route::get('/profil', [App\Http\Controllers\Siswa\DashboardController::class, 'profil'])->name('profil');
    Route::get('/konseling', [App\Http\Controllers\Siswa\DashboardController::class, 'konseling'])->name('konseling');

    // DNA Akademik Siswa 360°
    Route::prefix('dna')->name('dna.')->group(function () {
        Route::get('/', [App\Http\Controllers\Siswa\StudentDnaPortalController::class, 'index'])->name('index');
        Route::post('/diagnostic', [App\Http\Controllers\Siswa\StudentDnaPortalController::class, 'saveDiagnostic'])->name('diagnostic.save');
        Route::get('/pdf', [App\Http\Controllers\Siswa\StudentDnaPortalController::class, 'printPdf'])->name('pdf');
    });

    // Ekstrakurikuler Siswa (Kegiatan Non-Akademik)
    Route::prefix('ekskul')->name('ekskul.')->group(function () {
        Route::get('/', [App\Http\Controllers\Siswa\StudentExtracurricularController::class, 'index'])->name('index');
        Route::post('/{extracurricular}/claim', [App\Http\Controllers\Siswa\StudentExtracurricularController::class, 'claim'])->name('claim');
    });
 
    // CBT Routes (Siswa)
    Route::prefix('cbt')->name('cbt.')->middleware('feature:siswa_access_cbt')->group(function () {
        Route::get('/', [App\Http\Controllers\Siswa\CbtController::class, 'index'])->name('index');
        Route::get('/history', [App\Http\Controllers\Siswa\CbtController::class, 'history'])->name('history');
        Route::get('/{exam}', [App\Http\Controllers\Siswa\CbtController::class, 'show'])->name('show');
        Route::post('/{exam}/verify-access', [App\Http\Controllers\Siswa\CbtController::class, 'verifyAccess'])->name('verify-access');
        Route::post('/{exam}/start', [App\Http\Controllers\Siswa\CbtController::class, 'start'])->name('start');
        Route::post('/sessions/{session}/save-answer', [App\Http\Controllers\Siswa\CbtController::class, 'saveAnswer'])->name('save-answer')->middleware('throttle:120,1');
        Route::post('/sessions/{session}/tab-switch', [App\Http\Controllers\Siswa\CbtController::class, 'tabSwitch'])->name('tab-switch')->middleware('throttle:30,1');
        Route::get('/sessions/{session}/heartbeat', [App\Http\Controllers\Siswa\CbtController::class, 'heartbeat'])->name('heartbeat');
        Route::post('/sessions/{session}/submit', [App\Http\Controllers\Siswa\CbtController::class, 'submit'])->name('submit');
        Route::get('/{exam}/result', [App\Http\Controllers\Siswa\CbtController::class, 'result'])->name('result');
        Route::get('/sessions/{session}/review', [App\Http\Controllers\Siswa\CbtController::class, 'review'])->name('review');
    });
    // Siswa Games Route
    Route::post('/lms_games/{game}/finish', [App\Http\Controllers\Siswa\LmsController::class, 'finishGame'])->name('lms_games.finish');

    // Siswa LMS Routes
    Route::prefix('lms')->name('lms.')->middleware('feature:siswa_access_lms')->group(function () {
        Route::get('/', [App\Http\Controllers\Siswa\LmsController::class, 'index'])->name('index');
        Route::get('/catalog', [App\Http\Controllers\Siswa\LmsController::class, 'catalog'])->name('catalog');
        Route::post('/{course}/enroll', [App\Http\Controllers\Siswa\LmsController::class, 'enroll'])->name('enroll');
        Route::get('/{course}', [App\Http\Controllers\Siswa\LmsController::class, 'show'])->name('show');
        Route::get('/{course}/meeting/join', [App\Http\Controllers\Siswa\LmsController::class, 'joinMeeting'])->name('meeting.join');
        Route::post('/{course}/meeting/leave', [App\Http\Controllers\Siswa\LmsController::class, 'leaveAttendance'])->name('meeting.leave'); // AJAX
        Route::get('/live-status', [App\Http\Controllers\Siswa\LmsController::class, 'liveStatus'])->name('live-status'); // AJAX polling

        // Assignments & Quizzes
        Route::post('/assignments/{assignment}/submit', [App\Http\Controllers\Siswa\LmsController::class, 'submitAssignment'])->name('assignments.submit');
        Route::get('/quizzes/{quiz}/start', [App\Http\Controllers\Siswa\LmsController::class, 'startQuiz'])->name('quizzes.start');
        Route::post('/attempts/{attempt}/submit', [App\Http\Controllers\Siswa\LmsController::class, 'submitQuiz'])->name('quizzes.submit');
        Route::get('/attempts/{attempt}/result', [App\Http\Controllers\Siswa\LmsController::class, 'quizResult'])->name('quizzes.result');

        Route::post('/materials/{material}/track', [App\Http\Controllers\Siswa\LmsController::class, 'trackMaterial'])->name('materials.track');
        Route::post('/materials/{material}/react', [App\Http\Controllers\Siswa\LmsController::class, 'reactMaterial'])->name('materials.react');
        Route::get('/materials/{material}/download', [App\Http\Controllers\Siswa\LmsController::class, 'downloadMaterial'])->name('materials.download');
        Route::get('/materials/{material}/view', [App\Http\Controllers\Siswa\LmsController::class, 'viewMaterial'])->name('materials.view');
        Route::get('/materials/{material}/player', [App\Http\Controllers\Siswa\LmsController::class, 'playerMaterial'])->name('materials.player');
        Route::post('/materials/{material}/notes', [App\Http\Controllers\Siswa\LmsController::class, 'saveNote'])->name('materials.notes');

        // Discussions
        Route::get('/{course}/discussions', [App\Http\Controllers\Siswa\LmsController::class, 'discussions'])->name('discussions.index');
        Route::post('/{course}/discussions', [App\Http\Controllers\Siswa\LmsController::class, 'storeDiscussion'])->name('discussions.store');
        Route::get('/{course}/discussions/{discussion}', [App\Http\Controllers\Siswa\LmsController::class, 'showDiscussion'])->name('discussions.show');
        Route::get('/{course}/discussions/{discussion}/edit', [App\Http\Controllers\Siswa\LmsController::class, 'editDiscussion'])->name('discussions.edit');
        Route::put('/{course}/discussions/{discussion}', [App\Http\Controllers\Siswa\LmsController::class, 'updateDiscussion'])->name('discussions.update');
        Route::post('/{course}/discussions/{discussion}/reply', [App\Http\Controllers\Siswa\LmsController::class, 'replyDiscussion'])->name('discussions.reply');
    });

    // PKL Routes
    Route::prefix('pkl')->name('pkl.')->group(function () {
        Route::get('/', [App\Http\Controllers\Siswa\PklStudentController::class, 'index'])->name('index');
        Route::post('/log', [App\Http\Controllers\Siswa\PklStudentController::class, 'storeLog'])->name('log.store');
    });

    // Final Project / Tugas Akhir Routes (Siswa Class XII SMA/SMK)
    Route::prefix('final-project')->name('final-project.')->group(function () {
        Route::get('/', [App\Http\Controllers\Siswa\FinalProjectStudentController::class, 'index'])->name('index');
        Route::post('/propose', [App\Http\Controllers\Siswa\FinalProjectStudentController::class, 'propose'])->name('propose');
        Route::post('/log', [App\Http\Controllers\Siswa\FinalProjectStudentController::class, 'storeLog'])->name('log.store');
        Route::get('/download-format/{format}', [App\Http\Controllers\Siswa\FinalProjectStudentController::class, 'downloadFormat'])->name('download-format');
        Route::get('/guideline/download', [App\Http\Controllers\Siswa\FinalProjectStudentController::class, 'downloadGuideline'])->name('guideline.download');
    });

    // Raport Print Route (Siswa)
    Route::get('/raport/{reportCard}/print', [App\Http\Controllers\Siswa\DashboardController::class, 'printRaport'])->name('raport.print');

    // Surveys Kepuasan
    Route::get('surveys', [App\Http\Controllers\Respondent\SurveyParticipantController::class, 'index'])->name('surveys.index');
    Route::get('surveys/{survey}', [App\Http\Controllers\Respondent\SurveyParticipantController::class, 'take'])->name('surveys.take');
    Route::post('surveys/{survey}', [App\Http\Controllers\Respondent\SurveyParticipantController::class, 'submit'])->name('surveys.submit');
});
