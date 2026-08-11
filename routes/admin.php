<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Routes for SuperAdmin, Admin Sekolah, and Kepala Sekolah.
| Prefix: /admin   |  Name: admin.*
| Middleware: auth, role:superadmin,admin_sekolah,kepala_sekolah
*/

Route::prefix('admin')->name('admin.')->middleware('auth', 'role:superadmin,admin_sekolah,kepala_sekolah,panitia_cbt,panitia_pkl,panitia_ta,pks,piket,guru,guru_bk')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // Accessibility endpoint for automated checks (only in testing or when enabled via env)
    if (app()->environment('testing') || config('app.a11y_allow_public')) {
        Route::get('/a11y/admin-dashboard', function () {
            return view('admin.dashboard');
        })->name('a11y.admin.dashboard');
    }


    // Schools
    Route::resource('schools', App\Http\Controllers\Admin\SchoolController::class);

    // Master Data: Academic Years
    Route::resource('academic-years', App\Http\Controllers\Admin\AcademicYearController::class);
    Route::post('academic-years/{academic_year}/toggle-active', [App\Http\Controllers\Admin\AcademicYearController::class, 'toggleActive'])->name('academic-years.toggle');

    // Subject import routes (MUST be before resource to avoid {subject} catching 'import')
    Route::get('subjects/import', [App\Http\Controllers\Admin\SubjectController::class, 'importForm'])->name('subjects.import.form');
    Route::post('subjects/import', [App\Http\Controllers\Admin\SubjectController::class, 'import'])->name('subjects.import');
    Route::get('subjects/import/sample', [App\Http\Controllers\Admin\SubjectController::class, 'downloadSampleCsv'])->name('subjects.import.sample');
    // Subjects & Classrooms
    Route::resource('subjects', App\Http\Controllers\Admin\SubjectController::class);

    Route::resource('classrooms', App\Http\Controllers\Admin\ClassroomController::class);
    Route::get('classrooms/{classroom}/assign-students', [App\Http\Controllers\Admin\ClassroomController::class, 'assignStudentsForm'])->name('classrooms.assignStudents');
    Route::post('classrooms/{classroom}/assign-students', [App\Http\Controllers\Admin\ClassroomController::class, 'assignStudents']);
    Route::get('classrooms/{classroom}/assign-homeroom', [App\Http\Controllers\Admin\ClassroomController::class, 'assignHomeroomForm'])->name('classrooms.assignHomeroom');
    Route::post('classrooms/{classroom}/assign-homeroom', [App\Http\Controllers\Admin\ClassroomController::class, 'assignHomeroom'])->name('classrooms.assignHomeroom.store');

    // Semesters (Master Data)
    Route::resource('semesters', App\Http\Controllers\Admin\SemesterController::class);
    // Majors (Jurusan)
    Route::resource('majors', App\Http\Controllers\Admin\MajorController::class);
    // Program Keahlian SMK
    Route::resource('program-keahlians', App\Http\Controllers\Admin\ProgramKeahlianController::class);
    // Konsentrasi Keahlian SMK
    Route::resource('konsentrasi-keahlians', App\Http\Controllers\Admin\KonsentrasiKeahlianController::class);

    
    // Grade Weights (Bobot Nilai per Sekolah)
    Route::get('grade-weights', [App\Http\Controllers\Admin\GradeWeightController::class, 'index'])->name('grade-weights.index');
    Route::put('grade-weights/{school}', [App\Http\Controllers\Admin\GradeWeightController::class, 'update'])->name('grade-weights.update');
    Route::post('grade-weights/{school}/reset', [App\Http\Controllers\Admin\GradeWeightController::class, 'reset'])->name('grade-weights.reset');

    // Attendances (Absensi)
    Route::get('attendances/monitoring', [App\Http\Controllers\Admin\AttendanceController::class, 'monitoring'])->name('attendances.monitoring');
    Route::get('attendances/bulk', [App\Http\Controllers\Admin\AttendanceController::class, 'bulk'])->name('attendances.bulk');
    Route::post('attendances/bulk', [App\Http\Controllers\Admin\AttendanceController::class, 'bulkStore'])->name('attendances.bulkStore');
    Route::get('attendances/mass-update', [App\Http\Controllers\Admin\AttendanceController::class, 'massUpdate'])->name('attendances.mass-update');
    Route::post('attendances/mass-update', [App\Http\Controllers\Admin\AttendanceController::class, 'massUpdateStore'])->name('attendances.mass-update.store');
    Route::get('attendances/check', [App\Http\Controllers\Admin\AttendanceController::class, 'checkExistence'])->name('attendances.check');
    Route::resource('attendances', App\Http\Controllers\Admin\AttendanceController::class);

    // School endpoints (majors & keahlian)
    Route::get('schools/{school}/majors', [App\Http\Controllers\Admin\SchoolController::class, 'majors'])->name('schools.majors');
    Route::get('schools/{school}/keahlian', [App\Http\Controllers\Admin\SchoolController::class, 'keahlian'])->name('schools.keahlian');

    // AJAX: Get program keahlian by school
    Route::get('api/schools/{school}/program-keahlians', [App\Http\Controllers\Admin\ClassroomController::class, 'getProgramKeahlians'])->name('api.schools.program-keahlians');
    // AJAX: Get konsentrasi keahlian by program keahlian
    Route::get('api/program-keahlians/{programKeahlian}/konsentrasi-keahlians', [App\Http\Controllers\Admin\ClassroomController::class, 'getKonsentrasiKeahlians'])->name('api.program-keahlians.konsentrasi-keahlians');
    
    // PSB - Penerimaan Siswa Baru
    Route::prefix('psb')->name('psb.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ApplicantController::class, 'index'])->name('index');
        Route::get('/export', [App\Http\Controllers\Admin\ApplicantController::class, 'export'])->name('applicants.export');
        Route::resource('applicants', App\Http\Controllers\Admin\ApplicantController::class)->parameters([
            'applicants' => 'applicant'
        ]);
        
        // PSB Actions
        Route::post('applicants/{applicant}/verify-payment', [App\Http\Controllers\Admin\ApplicantController::class, 'verifyPayment'])->name('applicants.verify-payment');
        Route::post('applicants/{applicant}/verify-prestasi', [App\Http\Controllers\Admin\ApplicantController::class, 'verifyPrestasi'])->name('applicants.verify-prestasi');
        Route::post('applicants/{applicant}/reject-prestasi', [App\Http\Controllers\Admin\ApplicantController::class, 'rejectPrestasi'])->name('applicants.reject-prestasi');
        Route::post('applicants/{applicant}/verify-document', [App\Http\Controllers\Admin\ApplicantController::class, 'verifyDocument'])->name('applicants.verify-document');
        Route::get('applicants/{applicant}/input-score', [App\Http\Controllers\Admin\ApplicantController::class, 'inputScore'])->name('applicants.input-score');
        Route::post('applicants/{applicant}/save-score', [App\Http\Controllers\Admin\ApplicantController::class, 'saveScore'])->name('applicants.save-score');
        Route::post('applicants/{applicant}/accept', [App\Http\Controllers\Admin\ApplicantController::class, 'accept'])->name('applicants.accept');
        Route::post('applicants/{applicant}/reject', [App\Http\Controllers\Admin\ApplicantController::class, 'reject'])->name('applicants.reject');
        Route::post('applicants/{applicant}/migrate', [App\Http\Controllers\Admin\ApplicantController::class, 'migrate'])->name('applicants.migrate');
        
        // PSB Settings (Per-school)
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PsbSettingController::class, 'index'])->name('index');
            Route::get('/{school}/edit', [App\Http\Controllers\Admin\PsbSettingController::class, 'edit'])->name('edit');
            Route::put('/{school}', [App\Http\Controllers\Admin\PsbSettingController::class, 'update'])->name('update');
            
            // Fees management
            Route::post('/{school}/fees', [App\Http\Controllers\Admin\PsbSettingController::class, 'feeStore'])->name('fees.store');
            Route::delete('/fees/{fee}', [App\Http\Controllers\Admin\PsbSettingController::class, 'feeDestroy'])->name('fees.destroy');
            
            // Waves management
            Route::post('/{school}/waves', [App\Http\Controllers\Admin\PsbSettingController::class, 'waveStore'])->name('waves.store');
            Route::delete('/waves/{wave}', [App\Http\Controllers\Admin\PsbSettingController::class, 'waveDestroy'])->name('waves.destroy');
            Route::patch('/waves/{wave}/toggle', [App\Http\Controllers\Admin\PsbSettingController::class, 'waveToggle'])->name('waves.toggle');

            // Custom Document Types management
            Route::post('/{school}/custom-documents', [App\Http\Controllers\Admin\PsbSettingController::class, 'addCustomDocument'])->name('custom-documents.store');
            Route::delete('/{school}/custom-documents', [App\Http\Controllers\Admin\PsbSettingController::class, 'removeCustomDocument'])->name('custom-documents.destroy');
        });

        // PSB Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\PSBNotificationController::class, 'index'])->name('index');
            Route::post('/bulk', [App\Http\Controllers\Admin\PSBNotificationController::class, 'sendBulk'])->name('bulk');
            Route::get('/test', [App\Http\Controllers\Admin\PSBNotificationController::class, 'testConnection'])->name('test');
            Route::get('/preview', [App\Http\Controllers\Admin\PSBNotificationController::class, 'preview'])->name('preview');
            Route::post('/{applicant}/document-request', [App\Http\Controllers\Admin\PSBNotificationController::class, 'sendDocumentRequest'])->name('send-document-request');
            Route::post('/{id}/payment', [App\Http\Controllers\Admin\PSBNotificationController::class, 'sendPaymentConfirmation'])->name('send.payment');
            Route::post('/{id}/test-schedule', [App\Http\Controllers\Admin\PSBNotificationController::class, 'sendTestSchedule'])->name('send.test');
            Route::post('/{id}/acceptance', [App\Http\Controllers\Admin\PSBNotificationController::class, 'sendAcceptance'])->name('send.acceptance');
            Route::post('/{id}/custom', [App\Http\Controllers\Admin\PSBNotificationController::class, 'sendCustomMessage'])->name('send.custom');
            Route::post('/{id}/resend-registration', [App\Http\Controllers\Admin\PSBNotificationController::class, 'resendRegistration'])->name('resend.registration');
        });
    });
    
    // Teacher Competencies Management
    Route::get('teachers/{teacher}/competencies', [App\Http\Controllers\Admin\TeacherCompetencyController::class, 'index'])->name('teachers.competencies');
    Route::put('teachers/{teacher}/competencies', [App\Http\Controllers\Admin\TeacherCompetencyController::class, 'update'])->name('teachers.competencies.update');
    Route::post('teachers/{teacher}/competencies', [App\Http\Controllers\Admin\TeacherCompetencyController::class, 'store'])->name('teachers.competencies.store');
    Route::delete('teachers/{teacher}/competencies/{subject}', [App\Http\Controllers\Admin\TeacherCompetencyController::class, 'destroy'])->name('teachers.competencies.destroy');
    
    // AJAX: Get competent teachers by subject (for schedule grid)
    Route::post('api/teachers-by-subject', [App\Http\Controllers\Admin\TeacherCompetencyController::class, 'getTeachersBySubject'])->name('api.teachers-by-subject');
    
    // AJAX: Get subjects and teachers by classroom
    Route::post('api/schedule/by-classroom', [App\Http\Controllers\Admin\ScheduleGridController::class, 'getSubjectsAndTeachersByClassroom'])->name('api.schedule.by-classroom');
    
    // Time Slots Management (CRUD for jam pelajaran)
    Route::resource('time-slots', App\Http\Controllers\Admin\TimeSlotController::class);
    
    // Schedule Grid View (MUST be before resource routes)
    Route::get('schedules/grid', [App\Http\Controllers\Admin\ScheduleGridController::class, 'index'])->name('schedules.grid');
    Route::get('schedules/modal-data', [App\Http\Controllers\Admin\ScheduleGridController::class, 'getModalData'])->name('schedules.modal-data');
    Route::post('schedules/clear-cache', [App\Http\Controllers\Admin\ScheduleGridController::class, 'clearCache'])->name('schedules.clear-cache');
    Route::get('schedules/export', [App\Http\Controllers\Admin\ScheduleGridController::class, 'export'])->name('schedules.export');
    Route::get('schedules/print', [App\Http\Controllers\Admin\ScheduleGridController::class, 'print'])->name('schedules.print');
    Route::post('schedules/store-grid', [App\Http\Controllers\Admin\ScheduleGridController::class, 'store'])->name('schedules.store-grid');
    Route::get('schedules/{schedule}/edit-grid', [App\Http\Controllers\Admin\ScheduleGridController::class, 'edit'])->name('schedules.edit-grid');
    Route::put('schedules/{schedule}/update-grid', [App\Http\Controllers\Admin\ScheduleGridController::class, 'update'])->name('schedules.update-grid');
    Route::delete('schedules/{schedule}/delete-grid', [App\Http\Controllers\Admin\ScheduleGridController::class, 'destroy'])->name('schedules.destroy-grid');
    
    // Schedules
    Route::resource('schedules', App\Http\Controllers\Admin\ScheduleController::class);
    
    // Block Schedule Management (Sistem Blok - khusus SMK)
    Route::prefix('block-schedule')->name('block-schedule.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\BlockScheduleController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\BlockScheduleController::class, 'store'])->name('store');
        Route::put('/{blockSchedule}', [App\Http\Controllers\Admin\BlockScheduleController::class, 'update'])->name('update');
        Route::get('/groups/{classroom}', [App\Http\Controllers\Admin\BlockScheduleController::class, 'manageGroups'])->name('groups');
        Route::post('/groups/{classroom}/auto', [App\Http\Controllers\Admin\BlockScheduleController::class, 'autoAssignGroups'])->name('groups.auto');
        Route::post('/groups/{classroom}', [App\Http\Controllers\Admin\BlockScheduleController::class, 'saveGroups'])->name('groups.save');
        Route::get('/view', [App\Http\Controllers\Admin\BlockScheduleController::class, 'viewSchedule'])->name('view');
        Route::get('/swap-calendar', [App\Http\Controllers\Admin\BlockScheduleController::class, 'swapCalendar'])->name('swap-calendar');
    });

    // Teachers (Guru)
    Route::get('teachers/print-accounts', [App\Http\Controllers\Admin\TeacherController::class, 'printAccounts'])->name('teachers.print-accounts');
    Route::get('teachers/export-accounts', [App\Http\Controllers\Admin\TeacherController::class, 'exportAccounts'])->name('teachers.export-accounts');
    Route::post('teachers/reset-passwords', [App\Http\Controllers\Admin\TeacherController::class, 'resetPasswords'])->name('teachers.reset-passwords');
    Route::post('teachers/{teacher}/update-rfid', [App\Http\Controllers\Admin\TeacherController::class, 'updateRfid'])->name('teachers.update-rfid');

    // Absensi Manual Guru (oleh Admin) - harus sebelum resource route!
    Route::prefix('teachers/attendance')->name('teachers.attendance.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TeacherAttendanceController::class, 'index'])->name('index');
        Route::get('bulk', [App\Http\Controllers\Admin\TeacherAttendanceController::class, 'bulkInput'])->name('bulk');
        Route::post('bulk', [App\Http\Controllers\Admin\TeacherAttendanceController::class, 'bulkStore'])->name('bulk.store');
        Route::get('rekap', [App\Http\Controllers\Admin\TeacherAttendanceController::class, 'rekap'])->name('rekap');
        Route::delete('{attendance}', [App\Http\Controllers\Admin\TeacherAttendanceController::class, 'destroy'])->name('destroy');
    });

    Route::resource('teachers', App\Http\Controllers\Admin\TeacherController::class);

    // ══════ KEPEGAWAIAN MODULE ══════
    Route::prefix('employees')->name('employees.')->group(function () {
        // Dashboard SDM
        Route::get('dashboard/sdm', [App\Http\Controllers\Admin\EmployeeController::class, 'dashboard'])->name('dashboard');

        // Profile Lengkap Pegawai
        Route::get('{employee}/profile', [App\Http\Controllers\Admin\EmployeeController::class, 'profile'])->name('profile');

        // Cuti & Izin
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::get('rekap', [App\Http\Controllers\Admin\EmployeeLeaveController::class, 'rekap'])->name('rekap');
            Route::get('/', [App\Http\Controllers\Admin\EmployeeLeaveController::class, 'index'])->name('index');
            Route::get('create', [App\Http\Controllers\Admin\EmployeeLeaveController::class, 'create'])->name('create');
            Route::post('/', [App\Http\Controllers\Admin\EmployeeLeaveController::class, 'store'])->name('store');
            Route::get('{leave}', [App\Http\Controllers\Admin\EmployeeLeaveController::class, 'show'])->name('show');
            Route::post('{leave}/approve', [App\Http\Controllers\Admin\EmployeeLeaveController::class, 'approve'])->name('approve');
            Route::post('{leave}/reject', [App\Http\Controllers\Admin\EmployeeLeaveController::class, 'reject'])->name('reject');
        });

        // Absensi Pegawai
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [App\Http\Controllers\Admin\EmployeeAttendanceController::class, 'index'])->name('index');
            Route::get('monitoring', [App\Http\Controllers\Admin\EmployeeAttendanceController::class, 'monitoring'])->name('monitoring');
            Route::get('bulk', [App\Http\Controllers\Admin\EmployeeAttendanceController::class, 'bulkInput'])->name('bulk');
            Route::post('bulk', [App\Http\Controllers\Admin\EmployeeAttendanceController::class, 'bulkStore'])->name('bulk.store');
            Route::get('rekap', [App\Http\Controllers\Admin\EmployeeAttendanceController::class, 'rekap'])->name('rekap');
            Route::delete('{attendance}', [App\Http\Controllers\Admin\EmployeeAttendanceController::class, 'destroy'])->name('destroy');
        });

        // Sub-resources (inline CRUD on profile page)
        Route::post('{employee}/educations', [App\Http\Controllers\Admin\EmployeeController::class, 'storeEducation'])->name('educations.store');
        Route::delete('educations/{education}', [App\Http\Controllers\Admin\EmployeeController::class, 'destroyEducation'])->name('educations.destroy');
        Route::post('{employee}/trainings', [App\Http\Controllers\Admin\EmployeeController::class, 'storeTraining'])->name('trainings.store');
        Route::delete('trainings/{training}', [App\Http\Controllers\Admin\EmployeeController::class, 'destroyTraining'])->name('trainings.destroy');
        Route::post('{employee}/documents', [App\Http\Controllers\Admin\EmployeeController::class, 'storeDocument'])->name('documents.store');
        Route::delete('documents/{document}', [App\Http\Controllers\Admin\EmployeeController::class, 'destroyDocument'])->name('documents.destroy');
        Route::post('{employee}/family', [App\Http\Controllers\Admin\EmployeeController::class, 'storeFamily'])->name('family.store');
        Route::put('family/{member}', [App\Http\Controllers\Admin\EmployeeController::class, 'updateFamily'])->name('family.update');
        Route::delete('family/{member}', [App\Http\Controllers\Admin\EmployeeController::class, 'destroyFamily'])->name('family.destroy');
        Route::post('{employee}/contracts', [App\Http\Controllers\Admin\EmployeeController::class, 'storeContract'])->name('contracts.store');
        Route::put('contracts/{contract}', [App\Http\Controllers\Admin\EmployeeController::class, 'updateContract'])->name('contracts.update');
    });

    // Employees (Pegawai Non-Kependidikan)
    Route::post('employees/{employee}/update-rfid', [App\Http\Controllers\Admin\EmployeeController::class, 'updateRfid'])->name('employees.update-rfid');
    Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);

    // Master Data - Positions (Jabatan)
    Route::prefix('master')->name('master.')->group(function () {
        Route::resource('positions', App\Http\Controllers\Admin\PositionController::class);
        Route::post('positions/bulk-update-allowances', [App\Http\Controllers\Admin\PositionController::class, 'bulkUpdateAllowances'])->name('positions.bulk-update-allowances');
    });
    
    // Assignments (Penugasan)
    Route::prefix('assignments')->name('assignments.')->group(function () {
        // Position Assignments (Penugasan Jabatan)
        Route::get('positions', [App\Http\Controllers\Admin\PositionAssignmentController::class, 'index'])->name('positions.index');
        Route::get('positions/create', [App\Http\Controllers\Admin\PositionAssignmentController::class, 'create'])->name('positions.create');
        Route::post('positions', [App\Http\Controllers\Admin\PositionAssignmentController::class, 'store'])->name('positions.store');
        Route::get('positions/{employee}/edit', [App\Http\Controllers\Admin\PositionAssignmentController::class, 'edit'])->name('positions.edit');
        Route::put('positions/{employee}', [App\Http\Controllers\Admin\PositionAssignmentController::class, 'update'])->name('positions.update');
        Route::delete('positions/{employee}', [App\Http\Controllers\Admin\PositionAssignmentController::class, 'destroy'])->name('positions.destroy');
        Route::delete('positions/{employee}/position/{position}', [App\Http\Controllers\Admin\PositionAssignmentController::class, 'destroySinglePosition'])->name('positions.destroy-single');
        
        // Teaching Assignments (Penugasan Mengajar)
        Route::get('teaching', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'index'])->name('teaching.index');
        Route::get('teaching/create', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'create'])->name('teaching.create');
        Route::post('teaching', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'store'])->name('teaching.store');
        Route::get('teaching/{teacher}/edit', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'edit'])->name('teaching.edit');
        Route::put('teaching/{assignment}', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'update'])->name('teaching.update');
        Route::delete('teaching/{assignment}', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'destroy'])->name('teaching.destroy-single');
        Route::delete('teaching/{teacher}/bulk-destroy', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'bulkDestroy'])->name('teaching.bulk-destroy');
        Route::post('teaching/copy-to-semester', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'copyToSemester'])->name('teaching.copy-to-semester');
        Route::post('teaching/sync-from-schedules', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'syncFromSchedules'])->name('teaching.sync-from-schedules');
    });
    
    // AJAX: Get semesters filtered by academic year (for dynamic dropdowns)
    Route::get('api/semesters-by-year/{academicYear}', [App\Http\Controllers\Admin\TeachingAssignmentController::class, 'getSemestersByYear'])->name('api.semesters-by-year');
    
    // Parents (Orang Tua/Wali)
    Route::resource('parents', App\Http\Controllers\Admin\ParentController::class);
    
    // Financial Management
    Route::get('bills/bulk-create', [App\Http\Controllers\Admin\StudentBillController::class, 'bulkCreate'])->name('bills.bulk-create');
    Route::post('bills/bulk-store', [App\Http\Controllers\Admin\StudentBillController::class, 'bulkStore'])->name('bills.bulk-store');
    Route::post('bills/bulk-waive', [App\Http\Controllers\Admin\StudentBillController::class, 'bulkWaiveLateFee'])->name('bills.bulk-waive');
    Route::post('bills/bulk-update-amount', [App\Http\Controllers\Admin\StudentBillController::class, 'bulkUpdateAmount'])->name('bills.bulk-update-amount');
    Route::post('bills/bulk-delete', [App\Http\Controllers\Admin\StudentBillController::class, 'bulkDelete'])->name('bills.bulk-delete');
    Route::get('bills/export', [App\Http\Controllers\Admin\StudentBillController::class, 'export'])->name('bills.export');
    Route::resource('bills', App\Http\Controllers\Admin\StudentBillController::class);
    
    Route::get('payments/bulk-create', [App\Http\Controllers\Admin\PaymentController::class, 'bulkCreate'])->name('payments.bulk-create');
    Route::get('payments/fetch-bills', [App\Http\Controllers\Admin\PaymentController::class, 'fetchBills'])->name('payments.fetch-bills');
    Route::post('payments/bulk-store', [App\Http\Controllers\Admin\PaymentController::class, 'bulkStore'])->name('payments.bulk-store');
    Route::post('payments/batch-store', [App\Http\Controllers\Admin\PaymentController::class, 'batchStore'])->name('payments.batch-store');
    Route::get('payments/export', [App\Http\Controllers\Admin\PaymentController::class, 'export'])->name('payments.export');
    Route::get('payments/{payment}/receipt', [App\Http\Controllers\Admin\PaymentController::class, 'downloadReceipt'])->name('payments.receipt');
    Route::resource('payments', App\Http\Controllers\Admin\PaymentController::class);

    // Payment Reports (Laporan Rekap Status Tagihan Siswa)
    Route::get('payment-reports', [App\Http\Controllers\Admin\ReportController::class, 'index'])->name('payment_reports.index');
    Route::get('payment-reports/export', [App\Http\Controllers\Admin\ReportController::class, 'export'])->name('payment_reports.export');
    
    // Settings Management
    Route::get('settings/features', [App\Http\Controllers\Admin\SettingsController::class, 'features'])->name('settings.features');
    Route::put('settings/features', [App\Http\Controllers\Admin\SettingsController::class, 'updateFeatures'])->name('settings.features.update');
    Route::get('settings/late-fees', [App\Http\Controllers\Admin\SettingsController::class, 'lateFees'])->name('settings.late-fees');
    Route::put('settings/late-fees', [App\Http\Controllers\Admin\SettingsController::class, 'updateLateFees'])->name('settings.late-fees.update');
    Route::post('settings/late-fees/preview', [App\Http\Controllers\Admin\SettingsController::class, 'previewLateFee'])->name('settings.late-fees.preview');
    Route::get('settings/report-cards', [App\Http\Controllers\Admin\SettingsController::class, 'reportCards'])->name('settings.report-cards');
    Route::put('settings/report-cards', [App\Http\Controllers\Admin\SettingsController::class, 'updateReportCards'])->name('settings.report-cards.update');
    Route::get('settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    
    // Report Cards (Rapor Digital)
    Route::get('report-cards', [App\Http\Controllers\Admin\ReportCardController::class, 'index'])->name('report_cards.index');
    Route::post('report-cards/generate', [App\Http\Controllers\Admin\ReportCardController::class, 'generate'])->name('report_cards.generate');
    Route::get('report-cards/{reportCard}', [App\Http\Controllers\Admin\ReportCardController::class, 'show'])->name('report_cards.show');
    Route::get('report-cards/{reportCard}/edit', [App\Http\Controllers\Admin\ReportCardController::class, 'edit'])->name('report_cards.edit');
    Route::put('report-cards/{reportCard}', [App\Http\Controllers\Admin\ReportCardController::class, 'update'])->name('report_cards.update');
    Route::patch('report-cards/{reportCard}/finalize', [App\Http\Controllers\Admin\ReportCardController::class, 'finalize'])->name('report_cards.finalize');
    Route::patch('report-cards/{reportCard}/publish', [App\Http\Controllers\Admin\ReportCardController::class, 'publish'])->name('report_cards.publish');
    Route::get('report-cards/{reportCard}/print', [App\Http\Controllers\Admin\ReportCardController::class, 'print'])->name('report_cards.print');
    Route::post('report-cards/bulk-download', [App\Http\Controllers\Admin\ReportCardController::class, 'bulkDownload'])->name('report_cards.bulk_download');
    

    Route::prefix('promotions')->name('promotions.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\StudentLifecycleController::class, 'promotionIndex'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\StudentLifecycleController::class, 'promotionStore'])->name('store');
    });
    Route::get('alumni', [App\Http\Controllers\Admin\StudentLifecycleController::class, 'alumniIndex'])->name('alumni.index');

    // Student Counseling (Bimbingan Konseling)
    Route::prefix('counseling')->name('counseling.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\StudentCounselingController::class, 'index'])->name('index');
        Route::get('/search-students', [App\Http\Controllers\Admin\StudentCounselingController::class, 'searchStudents'])->name('search-students');
        Route::get('/create', [App\Http\Controllers\Admin\StudentCounselingController::class, 'create'])->name('create');
        Route::get('/create-prestasi', [App\Http\Controllers\Admin\StudentCounselingController::class, 'createPrestasi'])->name('create-prestasi');
        Route::get('/create-pembinaan', [App\Http\Controllers\Admin\StudentCounselingController::class, 'createPembinaan'])->name('create-pembinaan');
        Route::post('/', [App\Http\Controllers\Admin\StudentCounselingController::class, 'store'])->name('store');
        Route::get('/{record}', [App\Http\Controllers\Admin\StudentCounselingController::class, 'show'])->name('show');
        Route::get('/{record}/edit', [App\Http\Controllers\Admin\StudentCounselingController::class, 'edit'])->name('edit');
        Route::post('/{record}/action', [App\Http\Controllers\Admin\StudentCounselingController::class, 'processAction'])->name('action');
        Route::put('/{record}', [App\Http\Controllers\Admin\StudentCounselingController::class, 'update'])->name('update');
        Route::delete('/{record}', [App\Http\Controllers\Admin\StudentCounselingController::class, 'destroy'])->name('destroy');
    });
    


    // Workload & Salary Summary (Beban Kerja & Penggajian)
    Route::prefix('workload')->name('workload.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'index'])->name('index');
        Route::post('/{employee}/calculate', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'calculate'])->name('calculate');
        Route::post('/bulk-calculate', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'bulkCalculate'])->name('bulk-calculate');
        Route::get('/{employee}/salary-detail', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'salaryDetail'])->name('salary-detail');
        Route::get('/{employee}/salary-slip', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'salarySlip'])->name('salary-slip');
        Route::get('/{employee}/salary-slip-pdf', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'salarySlipPdf'])->name('salary-slip-pdf');
        Route::post('/{workload}/confirm', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'confirm'])->name('confirm');
        Route::post('/{workload}/lock', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'lock'])->name('lock');
        Route::get('/salary-report', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'salaryReport'])->name('salary-report');
        Route::get('/salary-report/export', [App\Http\Controllers\Admin\WorkloadSummaryController::class, 'exportSalaryReport'])->name('salary-report.export');
    });

    // Payroll Management (SDM & Penggajian)
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/slip-search', [App\Http\Controllers\Admin\PayrollController::class, 'slipSearch'])->name('slip-search');
        Route::get('/settings', [App\Http\Controllers\Admin\PayrollController::class, 'settings'])->name('settings');
        Route::put('/settings', [App\Http\Controllers\Admin\PayrollController::class, 'updateSettings'])->name('settings.update');
    });

    // Operational Expenses (Pengeluaran Operasional Non-SDM)
    Route::prefix('operational-expenses')->name('operational-expenses.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\OperationalExpenseController::class, 'index'])->name('index');
        Route::post('/', [App\Http\Controllers\Admin\OperationalExpenseController::class, 'store'])->name('store');
        Route::put('/{operationalExpense}', [App\Http\Controllers\Admin\OperationalExpenseController::class, 'update'])->name('update');
        Route::delete('/{operationalExpense}', [App\Http\Controllers\Admin\OperationalExpenseController::class, 'destroy'])->name('destroy');
        Route::post('/categories', [App\Http\Controllers\Admin\OperationalExpenseController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [App\Http\Controllers\Admin\OperationalExpenseController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [App\Http\Controllers\Admin\OperationalExpenseController::class, 'destroyCategory'])->name('categories.destroy');
    });

    // CBT Management (Admin)
    Route::prefix('cbt')->name('cbt.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\CbtManagementController::class, 'index'])->name('index');
        Route::get('/banks', [App\Http\Controllers\Admin\CbtManagementController::class, 'bankIndex'])->name('banks');
        Route::get('/banks/import-template', [App\Http\Controllers\Admin\CbtManagementController::class, 'downloadImportTemplate'])->name('banks.import-template');
        Route::post('/banks/import', [App\Http\Controllers\Admin\CbtManagementController::class, 'importBank'])->name('banks.import');
        Route::get('/report', [App\Http\Controllers\Admin\CbtManagementController::class, 'report'])->name('report');

        // Admin Exam CRUD (school-scope exams)
        Route::get('/exams/create', [App\Http\Controllers\Admin\CbtManagementController::class, 'examCreate'])->name('exams.create');
        Route::post('/exams', [App\Http\Controllers\Admin\CbtManagementController::class, 'examStore'])->name('exams.store');
        Route::post('/exams/{exam}/publish', [App\Http\Controllers\Admin\CbtManagementController::class, 'examPublish'])->name('exams.publish');
        Route::post('/exams/{exam}/activate', [App\Http\Controllers\Admin\CbtManagementController::class, 'examActivate'])->name('exams.activate');
        Route::post('/exams/{exam}/batch-start', [App\Http\Controllers\Admin\CbtManagementController::class, 'batchStart'])->name('exams.batch-start');
        Route::post('/exams/{exam}/pause', [App\Http\Controllers\Admin\CbtManagementController::class, 'examPause'])->name('exams.pause');
        Route::post('/exams/{exam}/resume', [App\Http\Controllers\Admin\CbtManagementController::class, 'examResume'])->name('exams.resume');

        Route::get('/{exam}', [App\Http\Controllers\Admin\CbtManagementController::class, 'show'])->name('show');
        Route::get('/{exam}/results', [App\Http\Controllers\Admin\CbtManagementController::class, 'results'])->name('results');
        Route::post('/{exam}/force-complete', [App\Http\Controllers\Admin\CbtManagementController::class, 'forceComplete'])->name('force-complete');
        Route::post('/{exam}/sync-grades', [App\Http\Controllers\Admin\CbtManagementController::class, 'syncGrades'])->name('sync-grades');
    });

    // LMS Monitoring (Admin)
    Route::prefix('lms')->name('lms.')->group(function () {
        Route::get('/monitoring', [App\Http\Controllers\Admin\LmsMonitoringController::class, 'index'])->name('monitoring');
        Route::get('/monitoring/teacher/{teacherId}', [App\Http\Controllers\Admin\LmsMonitoringController::class, 'teacherDetail'])->name('monitoring.teacher-detail');
    });

    // Pembda Knowledge & Media Monitoring (Admin/Kepsek/Yayasan)
    Route::get('/knowledge/monitoring', [App\Http\Controllers\Admin\KnowledgeMonitoringController::class, 'index'])->name('knowledge.monitoring');
    Route::delete('/knowledge/monitoring/{knowledge}', [App\Http\Controllers\Admin\KnowledgeMonitoringController::class, 'destroy'])->name('knowledge.destroy');

    // Reputation Management (Admin)
    Route::prefix('reputation')->name('reputation.')->group(function () {
        Route::get('/logs', [App\Http\Controllers\Admin\ReputationController::class, 'logs'])->name('logs');
        Route::delete('/logs/{log}', [App\Http\Controllers\Admin\ReputationController::class, 'destroy'])->name('logs.destroy');
        Route::get('/award', [App\Http\Controllers\Admin\ReputationController::class, 'awardForm'])->name('award.form');
        Route::post('/award', [App\Http\Controllers\Admin\ReputationController::class, 'award'])->name('award');
        Route::get('/search-users', [App\Http\Controllers\Admin\ReputationController::class, 'searchUsers'])->name('search-users');
        Route::resource('badges', App\Http\Controllers\Admin\ReputationBadgeController::class);
    });

    // Homepage Content Management (Berita & Galeri)
    Route::resource('news', App\Http\Controllers\Admin\NewsController::class);
    Route::post('news/{news}/toggle-publish', [App\Http\Controllers\Admin\NewsController::class, 'togglePublish'])->name('news.toggle-publish');
    Route::resource('gallery', App\Http\Controllers\Admin\GalleryController::class);
    Route::get('homepage-content', [App\Http\Controllers\Admin\HomepageContentController::class, 'index'])->name('homepage-content.index');
    Route::post('homepage-content', [App\Http\Controllers\Admin\HomepageContentController::class, 'update'])->name('homepage-content.update');

    // PKL & Hubungan Industri (Alumni)
    Route::prefix('pkl-alumni')->name('pkl-alumni.')->group(function () {
        // Master DUDI
        Route::resource('dudis', App\Http\Controllers\Admin\DudiController::class);

        // Placements CRUD
        Route::get('placements/map', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'placementsMap'])->name('placements.map');
        Route::get('placements', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'placementsIndex'])->name('placements.index');
        Route::get('placements/create', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'placementsCreate'])->name('placements.create');
        Route::post('placements', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'placementsStore'])->name('placements.store');
        Route::get('placements/{placement}', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'placementsShow'])->name('placements.show');
        Route::post('placements/{placement}/log/{log}/approve', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'approveLog'])->name('placements.log.approve');
        Route::get('placements/{placement}/edit', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'placementsEdit'])->name('placements.edit');
        Route::put('placements/{placement}', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'placementsUpdate'])->name('placements.update');
        Route::delete('placements/{placement}', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'placementsDestroy'])->name('placements.destroy');

        // Tracer studies
        Route::get('tracer-studies', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'tracerIndex'])->name('tracer.index');

        // Job postings CRUD
        Route::get('jobs', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'jobsIndex'])->name('jobs.index');
        Route::get('jobs/create', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'jobsCreate'])->name('jobs.create');
        Route::post('jobs', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'jobsStore'])->name('jobs.store');
        Route::get('jobs/{job}/edit', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'jobsEdit'])->name('jobs.edit');
        Route::put('jobs/{job}', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'jobsUpdate'])->name('jobs.update');
        Route::delete('jobs/{job}', [App\Http\Controllers\Admin\PklAlumniAdminController::class, 'jobsDestroy'])->name('jobs.destroy');

        // PKL Monitoring Reports (Admin/Kepsek)
        Route::get('monitorings', [App\Http\Controllers\Admin\PklMonitoringReportController::class, 'index'])->name('monitorings.index');
        Route::get('monitorings/{teacher}', [App\Http\Controllers\Admin\PklMonitoringReportController::class, 'show'])->name('monitorings.show');
    });

    // Direktori Ikatan Alumni (Legacy Alumni) - moved outside pkl-alumni. name group so it resolves to admin.alumni-directory.*
    Route::prefix('pkl-alumni')->group(function () {
        Route::get('alumni-directory', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'index'])->name('alumni-directory.index');
        Route::get('alumni-directory/create', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'create'])->name('alumni-directory.create');
        Route::post('alumni-directory', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'store'])->name('alumni-directory.store');
        Route::get('alumni-directory/{directory}', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'show'])->name('alumni-directory.show');
        Route::get('alumni-directory/{directory}/edit', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'edit'])->name('alumni-directory.edit');
        Route::put('alumni-directory/{directory}', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'update'])->name('alumni-directory.update');
        Route::post('alumni-directory/purge-spam', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'purgeSpam'])->name('alumni-directory.purge-spam');
        Route::post('alumni-directory/{directory}/toggle-approval', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'toggleApproval'])->name('alumni-directory.toggle-approval');
        Route::delete('alumni-directory/{directory}', [App\Http\Controllers\Admin\AlumniDirectoryController::class, 'destroy'])->name('alumni-directory.destroy');
    });

    // Final Projects / Tugas Akhir Admin routes
    Route::prefix('final-projects')->name('final-projects.')->group(function () {
        Route::get('/formats', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'formatsIndex'])->name('formats.index');
        Route::post('/formats', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'formatsStore'])->name('formats.store');
        Route::delete('/formats/{format}', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'formatsDestroy'])->name('formats.destroy');

        Route::get('/proposals', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'proposalsIndex'])->name('proposals.index');
        Route::get('/proposals/create', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'proposalsCreate'])->name('proposals.create');
        Route::post('/proposals/store', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'proposalsStore'])->name('proposals.store');
        Route::post('/proposals/{project}/assign', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'proposalsAssign'])->name('proposals.assign');

        Route::get('/exams', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'examsIndex'])->name('exams.index');
        Route::post('/exams/{project}/schedule', [App\Http\Controllers\Admin\FinalProjectAdminController::class, 'examsSchedule'])->name('exams.schedule');
    });

    // ══════ MODUL PELATIHAN ══════
    Route::resource('training-modules', App\Http\Controllers\Admin\TrainingModuleController::class);
    Route::post('training-modules/{training_module}/toggle-publish', [App\Http\Controllers\Admin\TrainingModuleController::class, 'togglePublish'])->name('training-modules.toggle-publish');
    Route::get('training-modules/{training_module}/download-pdf', [App\Http\Controllers\Admin\TrainingModuleController::class, 'download'])->name('training-modules.download');

    // Surveys Kepuasan
    Route::resource('surveys', App\Http\Controllers\Admin\SurveyController::class);
    Route::get('surveys/{survey}/questions', [App\Http\Controllers\Admin\SurveyController::class, 'questions'])->name('surveys.questions');
    Route::post('surveys/{survey}/questions', [App\Http\Controllers\Admin\SurveyController::class, 'storeQuestion'])->name('surveys.questions.store');
    Route::delete('surveys/questions/{question}', [App\Http\Controllers\Admin\SurveyController::class, 'destroyQuestion'])->name('surveys.questions.destroy');
    Route::get('surveys/{survey}/results', [App\Http\Controllers\Admin\SurveyController::class, 'results'])->name('surveys.results');
    Route::get('surveys/{survey}/results/pdf', [App\Http\Controllers\Admin\SurveyController::class, 'downloadPdf'])->name('surveys.results.pdf');
    Route::post('surveys/answers/{answer}/score', [App\Http\Controllers\Admin\SurveyController::class, 'updateEssayScore'])->name('surveys.answers.score');
    Route::delete('surveys/responses/{response}', [App\Http\Controllers\Admin\SurveyController::class, 'destroyResponse'])->name('surveys.responses.destroy');
});

// Student Management routes with broader role permissions
Route::prefix('admin')->name('admin.')->middleware('auth', 'role:superadmin,admin_sekolah,guru,siswa,orang_tua')->group(function () {
    Route::get('students/{student}/payments', [App\Http\Controllers\Admin\StudentController::class, 'paymentHistory'])->name('students.payments');

    // Student import routes (MUST be before resource to avoid {student} catching 'import')
    Route::get('students/print-accounts', [App\Http\Controllers\Admin\StudentController::class, 'printAccounts'])->name('students.print-accounts');
    Route::get('students/export-accounts', [App\Http\Controllers\Admin\StudentController::class, 'exportAccounts'])->name('students.export-accounts');
    Route::post('students/reset-passwords', [App\Http\Controllers\Admin\StudentController::class, 'resetPasswords'])->name('students.reset-passwords');
    Route::get('students/import', [App\Http\Controllers\Admin\StudentController::class, 'importForm'])->name('students.import.form');
    Route::post('students/import', [App\Http\Controllers\Admin\StudentController::class, 'import'])->name('students.import');
    Route::get('students/import/sample', [App\Http\Controllers\Admin\StudentController::class, 'downloadSampleExcel'])->name('students.import.sample');
    Route::post('students/{student}/update-rfid', [App\Http\Controllers\Admin\StudentController::class, 'updateRfid'])->name('students.update-rfid');
    Route::get('students/rfid-bulk', [App\Http\Controllers\Admin\StudentController::class, 'rfidBulk'])->name('students.rfid-bulk');
    Route::post('students/rfid-bulk-assign', [App\Http\Controllers\Admin\StudentController::class, 'rfidBulkAssign'])->name('students.rfid-bulk-assign');
    Route::resource('students', App\Http\Controllers\Admin\StudentController::class);
    Route::resource('grades', App\Http\Controllers\Admin\GradeController::class);

    // Student Lifecycle Management (Status & Kenaikan)
    Route::prefix('students/{student}/lifecycle')->name('students.lifecycle.')->group(function () {
        Route::get('/history', [App\Http\Controllers\Admin\StudentLifecycleController::class, 'statusHistory'])->name('history');
        Route::get('/transition', [App\Http\Controllers\Admin\StudentLifecycleController::class, 'transitionForm'])->name('transition');
        Route::post('/transition', [App\Http\Controllers\Admin\StudentLifecycleController::class, 'transition'])->name('transition.store');
    });

    // Student Development (Catatan Perkembangan)
    Route::prefix('students/{student}/development')->name('students.development.')->group(function () {
        Route::get('/profile', [App\Http\Controllers\Admin\StudentDevelopmentController::class, 'studentProfile'])->name('profile');
        Route::get('/notes', [App\Http\Controllers\Admin\StudentDevelopmentController::class, 'notes'])->name('notes');
        Route::post('/notes', [App\Http\Controllers\Admin\StudentDevelopmentController::class, 'storeNote'])->name('notes.store');
        Route::get('/recommendations', [App\Http\Controllers\Admin\StudentDevelopmentController::class, 'recommendations'])->name('recommendations');
        Route::post('/recommendations', [App\Http\Controllers\Admin\StudentDevelopmentController::class, 'storeRecommendation'])->name('recommendations.store');
        Route::put('/recommendations/{recommendation}', [App\Http\Controllers\Admin\StudentDevelopmentController::class, 'updateRecommendation'])->name('recommendations.update');
    });

    // Kalender Pendidikan
    Route::get('/calendar/print', [App\Http\Controllers\Admin\EducationalCalendarController::class, 'print'])->name('calendar.print');
    Route::get('/calendar', [App\Http\Controllers\Admin\EducationalCalendarController::class, 'index'])->name('calendar.index');
    Route::post('/calendar', [App\Http\Controllers\Admin\EducationalCalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/{calendar}', [App\Http\Controllers\Admin\EducationalCalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{calendar}', [App\Http\Controllers\Admin\EducationalCalendarController::class, 'destroy'])->name('calendar.destroy');

    // Unit Produksi TEFA (Bengkelin)
    Route::prefix('tefa')->name('tefa.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TefaController::class, 'index'])->name('index');
        Route::post('/employees', [App\Http\Controllers\Admin\TefaController::class, 'storeEmployee'])->name('employees.store');
        Route::put('/employees/{id}', [App\Http\Controllers\Admin\TefaController::class, 'updateEmployee'])->name('employees.update');
        Route::delete('/employees/{id}', [App\Http\Controllers\Admin\TefaController::class, 'destroyEmployee'])->name('employees.destroy');
        Route::post('/employees/{id}/register-rfid', [App\Http\Controllers\Admin\TefaController::class, 'registerRfid'])->name('employees.register-rfid');

        Route::get('/attendances', [App\Http\Controllers\Admin\TefaController::class, 'attendances'])->name('attendances');
        Route::post('/attendances', [App\Http\Controllers\Admin\TefaController::class, 'storeAttendance'])->name('attendances.store');
        Route::put('/attendances/{id}', [App\Http\Controllers\Admin\TefaController::class, 'updateAttendance'])->name('attendances.update');
        Route::delete('/attendances/{id}', [App\Http\Controllers\Admin\TefaController::class, 'destroyAttendance'])->name('attendances.destroy');
    });

    // Validasi Perjanjian Kinerja
    Route::prefix('performance-contracts')->name('performance_contracts.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PerformanceContractController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\PerformanceContractController::class, 'show'])->name('show');
        Route::get('/{id}/print', [App\Http\Controllers\Admin\PerformanceContractController::class, 'print'])->name('print');
        Route::post('/{id}/process', [App\Http\Controllers\Admin\PerformanceContractController::class, 'process'])->name('process');
        Route::delete('/{id}', [App\Http\Controllers\Admin\PerformanceContractController::class, 'destroy'])->name('destroy');
    });

    // Evaluasi Perjanjian Kinerja Akhir Semester
    Route::get('/performance-evaluations', [App\Http\Controllers\Admin\PerformanceEvaluationController::class, 'index'])->name('performance_evaluations.index');
    Route::get('/performance-evaluations/{contractId}/{semesterId}/evaluate', [App\Http\Controllers\Admin\PerformanceEvaluationController::class, 'evaluate'])->name('performance_evaluations.evaluate');
    // Surat Edaran Masuk (Unit Sekolah)
    Route::prefix('surat-edaran')->name('letters.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\FoundationLetterReaderController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\Admin\FoundationLetterReaderController::class, 'show'])->name('show');
        Route::get('/{id}/print', [App\Http\Controllers\Admin\FoundationLetterReaderController::class, 'print'])->name('print');
    });
});

// User Management routes with all role permissions (gates are handled by UserPolicy)
Route::prefix('admin')->name('admin.')->middleware('auth', 'role:superadmin,admin_sekolah,bendahara,ketua_yayasan,guru,siswa,orang_tua,pegawai,alumni')->group(function () {
    Route::resource('users', App\Http\Controllers\Admin\UserController::class);
    Route::get('users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPasswordForm'])->name('users.reset-password.form');
    Route::post('users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('users.reset-password');
});
