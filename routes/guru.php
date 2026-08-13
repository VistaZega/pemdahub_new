<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guru Routes
|--------------------------------------------------------------------------
| Routes for teachers (Guru).
| Prefix: /guru   |  Name: guru.*
| Middleware: auth, role:guru
*/

Route::prefix('guru')->name('guru.')->middleware('auth', 'role:guru,kepala_sekolah,pegawai')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Guru\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [App\Http\Controllers\Guru\EducationalCalendarController::class, 'index'])->name('calendar.index');
    Route::get('/jadwal', [App\Http\Controllers\Guru\DashboardController::class, 'jadwal'])->name('jadwal');
    Route::get('/kelas', [App\Http\Controllers\Guru\DashboardController::class, 'kelas'])->name('kelas');
    Route::get('/kelas/{classroom}/siswa', [App\Http\Controllers\Guru\DashboardController::class, 'siswaKelas'])->name('siswa-kelas');
    
    // Tagihan Siswa (Wali Kelas)
    Route::get('/tagihan-siswa', [App\Http\Controllers\Guru\StudentBillingController::class, 'index'])->name('tagihan-siswa');
    Route::get('/nilai', [App\Http\Controllers\Guru\DashboardController::class, 'nilai'])->name('nilai');
    Route::get('/nilai/details', [App\Http\Controllers\Guru\DashboardController::class, 'gradeDetails'])->name('nilai.details');
    
    // Nilai Input (Bulk Grade Entry)
    Route::get('/nilai/input', [App\Http\Controllers\Guru\NilaiController::class, 'inputForm'])->name('nilai.input');
    Route::post('/nilai/store-bulk', [App\Http\Controllers\Guru\NilaiController::class, 'storeBulk'])->name('nilai.store-bulk');
    Route::get('/nilai/summary', [App\Http\Controllers\Guru\NilaiController::class, 'summary'])->name('nilai.summary');
    Route::put('/nilai/{grade}', [App\Http\Controllers\Guru\NilaiController::class, 'update'])->name('nilai.update');
    Route::delete('/nilai/{grade}', [App\Http\Controllers\Guru\NilaiController::class, 'destroy'])->name('nilai.destroy');
    
    Route::get('/absensi', [App\Http\Controllers\Guru\DashboardController::class, 'absensi'])->name('absensi');
    Route::get('/absensi/print', [App\Http\Controllers\Guru\DashboardController::class, 'printRekap'])->name('absensi.print');
    Route::get('/absensi/input', [App\Http\Controllers\Guru\AttendanceController::class, 'create'])->name('absensi.input');
    Route::post('/absensi/store', [App\Http\Controllers\Guru\AttendanceController::class, 'store'])->name('absensi.store');
    Route::delete('/absensi/delete-date', [App\Http\Controllers\Guru\AttendanceController::class, 'destroyDate'])->name('absensi.destroyDate');
    Route::get('/absensi/saya', [App\Http\Controllers\Guru\DashboardController::class, 'absensiSaya'])->name('absensi.saya')->middleware('feature:pegawai_view_attendance_recap');
    Route::get('/profil', [App\Http\Controllers\Guru\DashboardController::class, 'profil'])->name('profil');

    // Raport (Wali Kelas)
    Route::prefix('raport')->name('raport.')->group(function () {
        Route::get('/', [App\Http\Controllers\Guru\ReportCardController::class, 'index'])->name('index');
        Route::post('/generate', [App\Http\Controllers\Guru\ReportCardController::class, 'generate'])->name('generate');
        Route::get('/{reportCard}', [App\Http\Controllers\Guru\ReportCardController::class, 'show'])->name('show');
        Route::get('/{reportCard}/edit', [App\Http\Controllers\Guru\ReportCardController::class, 'edit'])->name('edit');
        Route::put('/{reportCard}', [App\Http\Controllers\Guru\ReportCardController::class, 'update'])->name('update');
        Route::patch('/{reportCard}/finalize', [App\Http\Controllers\Guru\ReportCardController::class, 'finalize'])->name('finalize');
        Route::patch('/{reportCard}/publish', [App\Http\Controllers\Guru\ReportCardController::class, 'publish'])->name('publish');
        Route::get('/{reportCard}/print', [App\Http\Controllers\Guru\ReportCardController::class, 'print'])->name('print');
        Route::post('/bulk-download', [App\Http\Controllers\Guru\ReportCardController::class, 'bulkDownload'])->name('bulkDownload');
    });

    // CBT Routes (Guru)
    Route::prefix('cbt')->name('cbt.')->middleware('feature:guru_access_cbt')->group(function () {
        // Question Banks
        Route::get('/banks', [App\Http\Controllers\Guru\CbtController::class, 'bankIndex'])->name('banks.index');
        Route::get('/banks/create', [App\Http\Controllers\Guru\CbtController::class, 'bankCreate'])->name('banks.create');
        Route::post('/banks', [App\Http\Controllers\Guru\CbtController::class, 'bankStore'])->name('banks.store');
        Route::get('/banks/import-template', [App\Http\Controllers\Guru\CbtController::class, 'downloadImportTemplate'])->name('banks.import-template');
        Route::post('/banks/import', [App\Http\Controllers\Guru\CbtController::class, 'importBank'])->name('banks.import');
        Route::get('/banks/{bank}', [App\Http\Controllers\Guru\CbtController::class, 'bankShow'])->name('banks.show');
        Route::post('/banks/{bank}/assign-to-lms', [App\Http\Controllers\Guru\CbtController::class, 'assignToLms'])->name('banks.assign-to-lms');
        Route::get('/banks/{bank}/edit', [App\Http\Controllers\Guru\CbtController::class, 'bankEdit'])->name('banks.edit');
        Route::put('/banks/{bank}', [App\Http\Controllers\Guru\CbtController::class, 'bankUpdate'])->name('banks.update');
        Route::delete('/banks/{bank}', [App\Http\Controllers\Guru\CbtController::class, 'bankDestroy'])->name('banks.destroy');

        // Questions
        Route::get('/banks/{bank}/questions/create', [App\Http\Controllers\Guru\CbtController::class, 'questionCreate'])->name('questions.create');
        Route::post('/banks/{bank}/questions', [App\Http\Controllers\Guru\CbtController::class, 'questionStore'])->name('questions.store');
        Route::post('/banks/{bank}/questions/import', [App\Http\Controllers\Guru\CbtController::class, 'importQuestions'])->name('questions.import');
        Route::get('/questions/{question}/edit', [App\Http\Controllers\Guru\CbtController::class, 'questionEdit'])->name('questions.edit');
        Route::put('/questions/{question}', [App\Http\Controllers\Guru\CbtController::class, 'questionUpdate'])->name('questions.update');
        Route::delete('/questions/{question}', [App\Http\Controllers\Guru\CbtController::class, 'questionDestroy'])->name('questions.destroy');

        // Exams
        Route::get('/exams', [App\Http\Controllers\Guru\CbtController::class, 'examIndex'])->name('exams.index');
        Route::get('/exams/create', [App\Http\Controllers\Guru\CbtController::class, 'examCreate'])->name('exams.create');
        Route::post('/exams', [App\Http\Controllers\Guru\CbtController::class, 'examStore'])->name('exams.store');
        Route::get('/exams/{exam}', [App\Http\Controllers\Guru\CbtController::class, 'examShow'])->name('exams.show');
        Route::get('/exams/{exam}/edit', [App\Http\Controllers\Guru\CbtController::class, 'examEdit'])->name('exams.edit');
        Route::put('/exams/{exam}', [App\Http\Controllers\Guru\CbtController::class, 'examUpdate'])->name('exams.update');
        Route::delete('/exams/{exam}', [App\Http\Controllers\Guru\CbtController::class, 'examDestroy'])->name('exams.destroy');
        Route::post('/exams/{exam}/publish', [App\Http\Controllers\Guru\CbtController::class, 'examPublish'])->name('exams.publish');
        Route::post('/exams/{exam}/activate', [App\Http\Controllers\Guru\CbtController::class, 'examActivate'])->name('exams.activate');
        Route::post('/exams/{exam}/batch-start', [App\Http\Controllers\Guru\CbtController::class, 'examBatchStart'])->name('exams.batch-start');
        Route::post('/exams/{exam}/complete', [App\Http\Controllers\Guru\CbtController::class, 'examComplete'])->name('exams.complete');
        Route::post('/exams/{exam}/pause', [App\Http\Controllers\Guru\CbtController::class, 'examPause'])->name('exams.pause');
        Route::post('/exams/{exam}/resume', [App\Http\Controllers\Guru\CbtController::class, 'examResume'])->name('exams.resume');
        Route::get('/exams/{exam}/results', [App\Http\Controllers\Guru\CbtController::class, 'examResults'])->name('exams.results');
        Route::post('/exams/{exam}/sync-grades', [App\Http\Controllers\Guru\CbtController::class, 'syncGrades'])->name('exams.sync-grades');

        // Essay Grading
        Route::get('/exams/{exam}/grade-essays', [App\Http\Controllers\Guru\CbtController::class, 'gradeEssays'])->name('exams.grade-essays');
        Route::post('/answers/{answer}/grade', [App\Http\Controllers\Guru\CbtController::class, 'gradeEssayStore'])->name('answers.grade');
    });

    // LMS Routes
    Route::prefix('lms')->name('lms.')->middleware('feature:guru_access_lms')->group(function () {
        // Courses
        Route::get('/', [App\Http\Controllers\Guru\LmsCourseController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Guru\LmsCourseController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Guru\LmsCourseController::class, 'store'])->name('store');
        Route::get('/{course}', [App\Http\Controllers\Guru\LmsCourseController::class, 'show'])->name('show');
        Route::get('/{course}/analytics', [App\Http\Controllers\Guru\LmsCourseController::class, 'analytics'])->name('analytics');
        Route::get('/{course}/export-gradebook', [App\Http\Controllers\Guru\LmsCourseController::class, 'exportGradebook'])->name('export-gradebook');
        Route::post('/replies/{reply}/best-answer', [App\Http\Controllers\Guru\LmsCourseController::class, 'markBestReply'])->name('replies.best-answer');
        Route::get('/{course}/edit', [App\Http\Controllers\Guru\LmsCourseController::class, 'edit'])->name('edit');
        Route::put('/{course}', [App\Http\Controllers\Guru\LmsCourseController::class, 'update'])->name('update');
        Route::delete('/{course}', [App\Http\Controllers\Guru\LmsCourseController::class, 'destroy'])->name('destroy');

        // Meeting / Video Conference
        Route::post('/{course}/meeting/start', [App\Http\Controllers\Guru\LmsCourseController::class, 'startMeeting'])->name('meeting.start');
        Route::post('/{course}/meeting/stop', [App\Http\Controllers\Guru\LmsCourseController::class, 'stopMeeting'])->name('meeting.stop');
        Route::get('/{course}/meeting/join', [App\Http\Controllers\Guru\LmsCourseController::class, 'joinMeeting'])->name('meeting.join');
        Route::get('/{course}/meeting/attendees', [App\Http\Controllers\Guru\LmsCourseController::class, 'meetingAttendees'])->name('meeting.attendees'); // AJAX polling
        Route::get('/{course}/meeting/attendance-report', [App\Http\Controllers\Guru\LmsCourseController::class, 'attendanceReport'])->name('meeting.attendance');

        // Modules
        Route::get('/{course}/modules/create', [App\Http\Controllers\Guru\LmsCourseController::class, 'createModule'])->name('modules.create');
        Route::post('/{course}/modules', [App\Http\Controllers\Guru\LmsCourseController::class, 'storeModule'])->name('modules.store');
        Route::get('/modules/{module}/edit', [App\Http\Controllers\Guru\LmsCourseController::class, 'editModule'])->name('modules.edit');
        Route::put('/modules/{module}', [App\Http\Controllers\Guru\LmsCourseController::class, 'updateModule'])->name('modules.update');
        Route::delete('/modules/{module}', [App\Http\Controllers\Guru\LmsCourseController::class, 'destroyModule'])->name('modules.destroy');

        // Materials (linked directly to course)
        Route::post('/upload-editor-image', [App\Http\Controllers\Guru\LmsCourseController::class, 'uploadEditorImage'])->name('upload-editor-image');
        Route::post('/materials/generate-ai', [App\Http\Controllers\Guru\LmsCourseController::class, 'generateAiMaterial'])->name('materials.generate-ai');
        Route::post('/{course}/materials', [App\Http\Controllers\Guru\LmsCourseController::class, 'storeMaterial'])->name('materials.store');
        Route::delete('/materials/{material}', [App\Http\Controllers\Guru\LmsCourseController::class, 'destroyMaterial'])->name('materials.destroy');
        Route::put('/materials/{material}', [App\Http\Controllers\Guru\LmsCourseController::class, 'updateMaterial'])->name('materials.update');
        Route::get('/materials/{material}/download', [App\Http\Controllers\Guru\LmsCourseController::class, 'downloadMaterial'])->name('materials.download');
        Route::get('/materials/{material}/view', [App\Http\Controllers\Guru\LmsCourseController::class, 'viewMaterial'])->name('materials.view');

        // Assignments
        Route::get('/{course}/assignments/create', [App\Http\Controllers\Guru\LmsAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('/{course}/assignments', [App\Http\Controllers\Guru\LmsAssignmentController::class, 'store'])->name('assignments.store');
        Route::get('/assignments/{assignment}', [App\Http\Controllers\Guru\LmsAssignmentController::class, 'show'])->name('assignments.show');
        Route::get('/assignments/{assignment}/edit', [App\Http\Controllers\Guru\LmsAssignmentController::class, 'edit'])->name('assignments.edit');
        Route::put('/assignments/{assignment}', [App\Http\Controllers\Guru\LmsAssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('/assignments/{assignment}', [App\Http\Controllers\Guru\LmsAssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::post('/submissions/{submission}/grade', [App\Http\Controllers\Guru\LmsAssignmentController::class, 'grade'])->name('submissions.grade');
        Route::get('/submissions/{submission}/download', [App\Http\Controllers\Guru\LmsAssignmentController::class, 'downloadSubmission'])->name('submissions.download');

        // Quizzes
        Route::get('/{course}/quizzes/create', [App\Http\Controllers\Guru\LmsQuizController::class, 'create'])->name('quizzes.create');
        Route::post('/{course}/quizzes', [App\Http\Controllers\Guru\LmsQuizController::class, 'store'])->name('quizzes.store');
        Route::get('/quizzes/{quiz}', [App\Http\Controllers\Guru\LmsQuizController::class, 'show'])->name('quizzes.show');
        Route::get('/quizzes/{quiz}/edit', [App\Http\Controllers\Guru\LmsQuizController::class, 'edit'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [App\Http\Controllers\Guru\LmsQuizController::class, 'update'])->name('quizzes.update');
        Route::delete('/quizzes/{quiz}', [App\Http\Controllers\Guru\LmsQuizController::class, 'destroy'])->name('quizzes.destroy');
        Route::post('/quizzes/{quiz}/questions', [App\Http\Controllers\Guru\LmsQuizController::class, 'storeQuestion'])->name('quizzes.questions.store');
        Route::get('/quizzes/{quiz}/template', [App\Http\Controllers\Guru\LmsQuizController::class, 'downloadTemplate'])->name('quizzes.template');
        Route::post('/quizzes/{quiz}/import', [App\Http\Controllers\Guru\LmsQuizController::class, 'importQuestions'])->name('quizzes.questions.import');
        Route::put('/questions/{question}', [App\Http\Controllers\Guru\LmsQuizController::class, 'updateQuestion'])->name('questions.update');
        Route::delete('/questions/{question}', [App\Http\Controllers\Guru\LmsQuizController::class, 'destroyQuestion'])->name('questions.destroy');
        Route::get('/quizzes/{quiz}/results', [App\Http\Controllers\Guru\LmsQuizController::class, 'results'])->name('quizzes.results');
        Route::post('/quizzes/{quiz}/toggle-publish', [App\Http\Controllers\Guru\LmsQuizController::class, 'togglePublish'])->name('quizzes.togglePublish');
        Route::post('/quizzes/{quiz}/sync-from-bank', [App\Http\Controllers\Guru\LmsQuizController::class, 'syncFromBank'])->name('quizzes.syncFromBank');
        Route::get('/quizzes/attempts/{attempt}', [App\Http\Controllers\Guru\LmsQuizController::class, 'showAttempt'])->name('quizzes.attempts.show');
        Route::post('/quizzes/attempts/{attempt}/grade', [App\Http\Controllers\Guru\LmsQuizController::class, 'gradeAttempt'])->name('quizzes.attempts.grade');

        // Discussions
        Route::get('/{course}/discussions', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'index'])->name('discussions.index');
        Route::post('/{course}/discussions', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'store'])->name('discussions.store');
        Route::get('/{course}/discussions/{discussion}', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'show'])->name('discussions.show');
        Route::get('/{course}/discussions/{discussion}/edit', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'edit'])->name('discussions.edit');
        Route::put('/{course}/discussions/{discussion}', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'update'])->name('discussions.update');
        Route::post('/{course}/discussions/{discussion}/reply', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'reply'])->name('discussions.reply');
        Route::post('/{course}/discussions/{discussion}/toggle-pin', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'togglePin'])->name('discussions.togglePin');
        Route::post('/{course}/discussions/{discussion}/toggle-lock', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'toggleLock'])->name('discussions.toggleLock');
        Route::post('/{course}/discussions/{discussion}/best-answer/{reply}', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'markBestAnswer'])->name('discussions.bestAnswer');
        Route::delete('/{course}/discussions/{discussion}', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'destroy'])->name('discussions.destroy');

        // Announcements
        Route::post('/{course}/announcements', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'storeAnnouncement'])->name('announcements.store');
        Route::delete('/announcements/{announcement}', [App\Http\Controllers\Guru\LmsDiscussionController::class, 'destroyAnnouncement'])->name('announcements.destroy');

        // Enrollment Management
        Route::get('/{course}/students', [App\Http\Controllers\Guru\LmsCourseController::class, 'enrolledStudents'])->name('students.index');
        Route::post('/{course}/students', [App\Http\Controllers\Guru\LmsCourseController::class, 'enrollStudents'])->name('students.enroll');
        Route::delete('/{course}/students/{student}', [App\Http\Controllers\Guru\LmsCourseController::class, 'unenrollStudent'])->name('students.unenroll');

        // Arcade (Games)
        Route::post('/games', [App\Http\Controllers\Guru\LmsGameController::class, 'store'])->name('games.store');
        Route::put('/games/{lms_game}', [App\Http\Controllers\Guru\LmsGameController::class, 'update'])->name('games.update');
        Route::delete('/games/{lms_game}', [App\Http\Controllers\Guru\LmsGameController::class, 'destroy'])->name('games.destroy');
        
        // Live Game (Kahoot/Mentimeter Clone)
        Route::post('/games/{game}/live', [App\Http\Controllers\LmsLiveController::class, 'createRoom'])->name('games.live.create');
        Route::get('/live/{session}/host', [App\Http\Controllers\LmsLiveController::class, 'hostUI'])->name('live.host');
        Route::post('/live/{session}/state', [App\Http\Controllers\LmsLiveController::class, 'updateState'])->name('live.state');
        Route::get('/live/{session}/poll', [App\Http\Controllers\LmsLiveController::class, 'pollHost'])->name('live.poll');
    });

    // PKL Monitoring Routes (Guru)
    Route::prefix('pkl')->name('pkl.')->group(function () {
        Route::get('/', [App\Http\Controllers\Guru\PklTeacherController::class, 'index'])->name('index');
        Route::get('/{placement}', [App\Http\Controllers\Guru\PklTeacherController::class, 'show'])->name('show');
        Route::post('/{placement}/log/{log}/approve', [App\Http\Controllers\Guru\PklTeacherController::class, 'approveLog'])->name('log.approve');
    });

    Route::prefix('pkl-monitorings')->name('pkl_monitorings.')->group(function () {
        Route::get('/', [App\Http\Controllers\Guru\PklMonitoringController::class, 'index'])->name('index');
        Route::get('/{dudi_id}/{shift?}', [App\Http\Controllers\Guru\PklMonitoringController::class, 'show'])->name('show');
        Route::post('/{dudi_id}/{shift?}', [App\Http\Controllers\Guru\PklMonitoringController::class, 'store'])->name('store');
        Route::post('/{dudi_id}/{shift?}/perangkat', [App\Http\Controllers\Guru\PklMonitoringController::class, 'updatePerangkat'])->name('update-perangkat');
    });

    // Final Project / Tugas Akhir Bimbingan & Ujian Routes (Guru)
    Route::prefix('final-projects')->name('final-projects.')->group(function () {
        Route::get('/bimbingan', [App\Http\Controllers\Guru\FinalProjectTeacherController::class, 'bimbinganIndex'])->name('bimbingan.index');
        Route::get('/bimbingan/{project}', [App\Http\Controllers\Guru\FinalProjectTeacherController::class, 'bimbinganShow'])->name('bimbingan.show');
        Route::post('/bimbingan/{project}/review-log/{log}', [App\Http\Controllers\Guru\FinalProjectTeacherController::class, 'reviewLog'])->name('bimbingan.review-log');
        Route::post('/bimbingan/{project}/ready', [App\Http\Controllers\Guru\FinalProjectTeacherController::class, 'markReadyForExam'])->name('bimbingan.ready');
        
        Route::get('/ujian', [App\Http\Controllers\Guru\FinalProjectTeacherController::class, 'ujianIndex'])->name('ujian.index');
        Route::post('/ujian/{project}/grade', [App\Http\Controllers\Guru\FinalProjectTeacherController::class, 'gradeProject'])->name('ujian.grade');
    });

    // AI Assistants (Asisten AI)
    Route::prefix('ai')->name('ai.')->group(function () {
        // RPP Generator
        Route::get('/lesson-plan', [App\Http\Controllers\Guru\AiLessonPlanController::class, 'index'])->name('lesson-plan');
        Route::post('/lesson-plan/generate', [App\Http\Controllers\Guru\AiLessonPlanController::class, 'generate'])->name('lesson-plan.generate');
        Route::post('/lesson-plan/download', [App\Http\Controllers\Guru\AiLessonPlanController::class, 'download'])->name('lesson-plan.download');

        // CBT Question Generator
        Route::get('/question-generator', [App\Http\Controllers\Guru\AiQuestionGeneratorController::class, 'index'])->name('question-generator');
        Route::post('/question-generator/generate', [App\Http\Controllers\Guru\AiQuestionGeneratorController::class, 'generate'])->name('question-generator.generate');
        Route::post('/question-generator/save', [App\Http\Controllers\Guru\AiQuestionGeneratorController::class, 'save'])->name('question-generator.save');
    });

    // Employee Leave / Cuti Mandiri Routes
    Route::middleware('feature:pegawai_can_request_leave')->group(function () {
        Route::get('/leaves', [App\Http\Controllers\Guru\EmployeeLeaveController::class, 'index'])->name('leaves.index');
        Route::get('/leaves/create', [App\Http\Controllers\Guru\EmployeeLeaveController::class, 'create'])->name('leaves.create');
        Route::post('/leaves', [App\Http\Controllers\Guru\EmployeeLeaveController::class, 'store'])->name('leaves.store');
    });

    // Surveys Kepuasan
    Route::get('surveys', [App\Http\Controllers\Respondent\SurveyParticipantController::class, 'index'])->name('surveys.index');
    Route::get('surveys/{survey}', [App\Http\Controllers\Respondent\SurveyParticipantController::class, 'take'])->name('surveys.take');
    Route::post('surveys/{survey}', [App\Http\Controllers\Respondent\SurveyParticipantController::class, 'submit'])->name('surveys.submit');

    // Perjanjian Kinerja Guru
    Route::prefix('performance-contracts')->name('performance_contracts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Guru\PerformanceContractController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Guru\PerformanceContractController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Guru\PerformanceContractController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\Guru\PerformanceContractController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\Guru\PerformanceContractController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\Guru\PerformanceContractController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/print', [App\Http\Controllers\Guru\PerformanceContractController::class, 'print'])->name('print');
        Route::get('/{id}', [App\Http\Controllers\Guru\PerformanceContractController::class, 'show'])->name('show');
    });

    // Pembda Knowledge & Media (Guru Space)
    Route::resource('knowledge', App\Http\Controllers\Guru\KnowledgeMediaController::class);
});

