<?php

use App\Http\Controllers\AdminAcademicAssignmentController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminExamSessionController;
use App\Http\Controllers\AdminSchoolController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FoundationDashboardController;
use App\Http\Controllers\FoundationSchoolReportController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Controllers\PortalProfileController;
use App\Http\Controllers\StudentExamController;
use App\Http\Controllers\StudentResultsController;
use App\Http\Controllers\StudentSubjectController;
use App\Http\Controllers\StudentSubjectMaterialController;
use App\Http\Controllers\SubjectMaterialDownloadController;
use App\Http\Controllers\TeacherExamController;
use App\Http\Controllers\TeacherMonitoringController;
use App\Http\Controllers\TeacherQuestionBankController;
use App\Http\Controllers\TeacherResultsController;
use App\Http\Controllers\TeacherSubjectController;
use App\Http\Controllers\TeacherSubjectMaterialController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home.index')->name('home');

Route::get('/login', [AuthenticatedSessionController::class, 'redirectToStudentLogin'])->name('login');
Route::get('/login/guru', [AuthenticatedSessionController::class, 'showTeacherLogin'])->name('login.teacher');
Route::post('/login/guru', [AuthenticatedSessionController::class, 'storeTeacherLogin'])
    ->middleware('throttle:portal-login')
    ->name('login.teacher.store');
Route::get('/login/siswa', [AuthenticatedSessionController::class, 'showStudentLogin'])->name('login.student');
Route::post('/login/siswa', [AuthenticatedSessionController::class, 'storeStudentLogin'])
    ->middleware('throttle:portal-login')
    ->name('login.student.store');
Route::get('/login/admin', [AuthenticatedSessionController::class, 'showAdminLogin'])->name('login.admin');
Route::post('/login/admin', [AuthenticatedSessionController::class, 'storeAdminLogin'])
    ->middleware('throttle:portal-login')
    ->name('login.admin.store');
Route::get('/login/yayasan', [AuthenticatedSessionController::class, 'showFoundationLogin'])->name('login.foundation');
Route::post('/login/yayasan', [AuthenticatedSessionController::class, 'storeFoundationLogin'])
    ->middleware('throttle:portal-login')->name('login.foundation.store');

Route::middleware('auth')->group(function (): void {
    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin_remote')->name('remote.dashboard');
    Route::get('/admin/sesi-ujian', [AdminExamSessionController::class, 'index'])
        ->middleware('role:admin_remote')->name('admin.exam-sessions');
    Route::delete('/admin/sesi-ujian/{exam}', [AdminExamSessionController::class, 'destroy'])
        ->middleware('role:admin_remote')->name('admin.exam-sessions.destroy');
    Route::patch('/admin/sesi-ujian/{exam}/arsip', [AdminExamSessionController::class, 'archive'])
        ->middleware('role:admin_remote')->name('admin.exam-sessions.archive');
    Route::patch('/admin/sesi-ujian/{exam}/pulihkan', [AdminExamSessionController::class, 'restore'])
        ->middleware('role:admin_remote')->name('admin.exam-sessions.restore');
    Route::get('/yayasan', [FoundationDashboardController::class, 'index'])
        ->middleware('role:admin_yayasan')->name('foundation.dashboard');
    Route::get('/yayasan/sekolah', [FoundationSchoolReportController::class, 'directory'])
        ->middleware('role:admin_yayasan')->name('foundation.schools');
    Route::get('/yayasan/laporan', [FoundationSchoolReportController::class, 'report'])
        ->middleware('role:admin_yayasan')->name('foundation.reports');
    Route::get('/yayasan/laporan/unduh', [FoundationSchoolReportController::class, 'export'])
        ->middleware('role:admin_yayasan')->name('foundation.reports.export');
    Route::get('/yayasan/sekolah/{school}', [FoundationSchoolReportController::class, 'show'])
        ->middleware('role:admin_yayasan')->name('foundation.schools.show');
    Route::get('/admin/pengguna', [AdminUserController::class, 'index'])
        ->middleware('role:admin_remote')
        ->name('admin.users');
    Route::get('/admin/pengguna/template', [AdminUserController::class, 'template'])
        ->middleware('role:admin_remote')
        ->name('admin.users.template');
    Route::post('/admin/pengguna/impor', [AdminUserController::class, 'import'])
        ->middleware('role:admin_remote')
        ->name('admin.users.import');
    Route::post('/admin/pengguna', [AdminUserController::class, 'store'])
        ->middleware('role:admin_remote')
        ->name('admin.users.store');
    Route::put('/admin/pengguna/{user}', [AdminUserController::class, 'update'])
        ->middleware('role:admin_remote')
        ->name('admin.users.update');
    Route::delete('/admin/pengguna/{user}', [AdminUserController::class, 'destroy'])
        ->middleware('role:admin_remote')
        ->name('admin.users.destroy');
    Route::get('/admin/penugasan', [AdminAcademicAssignmentController::class, 'index'])
        ->middleware('role:admin_remote')
        ->name('admin.assignments');
    Route::post('/admin/penugasan', [AdminAcademicAssignmentController::class, 'store'])
        ->middleware('role:admin_remote')
        ->name('admin.assignments.store');
    Route::put('/admin/penugasan/{subject}', [AdminAcademicAssignmentController::class, 'update'])
        ->middleware('role:admin_remote')
        ->name('admin.assignments.update');
    Route::post('/admin/penugasan/{subject}/jadwal', [AdminAcademicAssignmentController::class, 'storeSchedule'])
        ->middleware('role:admin_remote')
        ->name('admin.assignments.schedules.store');
    Route::delete('/admin/penugasan/{subject}/jadwal/{schedule}', [AdminAcademicAssignmentController::class, 'destroySchedule'])
        ->middleware('role:admin_remote')
        ->name('admin.assignments.schedules.destroy');
    Route::get('/admin/sekolah', [AdminSchoolController::class, 'index'])
        ->middleware('role:admin_remote')
        ->name('admin.schools');
    Route::post('/admin/sekolah', [AdminSchoolController::class, 'store'])
        ->middleware('role:admin_remote')
        ->name('admin.schools.store');
    Route::post('/admin/sekolah/{school}/rombel', [AdminSchoolController::class, 'storeClassroom'])
        ->middleware('role:admin_remote')
        ->name('admin.schools.classrooms.store');
    Route::put('/admin/rombel/{classroom}', [AdminSchoolController::class, 'updateClassroom'])
        ->middleware('role:admin_remote')
        ->name('admin.classrooms.update');
    Route::get('/admin/rombel/{classroom}/siswa', [AdminSchoolController::class, 'students'])
        ->middleware('role:admin_remote')
        ->name('admin.classrooms.students');
    Route::post('/admin/rombel/{classroom}/siswa', [AdminSchoolController::class, 'enrollStudent'])
        ->middleware('role:admin_remote')
        ->name('admin.classrooms.students.store');
    Route::delete('/admin/rombel/{classroom}/siswa/{student}', [AdminSchoolController::class, 'removeStudent'])
        ->middleware('role:admin_remote')
        ->name('admin.classrooms.students.destroy');
    Route::get('/admin/bank-soal', [TeacherQuestionBankController::class, 'index'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank');
    Route::get('/admin/bank-soal/buat', [TeacherQuestionBankController::class, 'create'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.create');
    Route::post('/admin/bank-soal/buat', [TeacherQuestionBankController::class, 'storeManual'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.store-manual');
    Route::post('/admin/bank-soal/impor', [TeacherQuestionBankController::class, 'import'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.import');
    Route::get('/admin/bank-soal/paket/{exam}', [TeacherQuestionBankController::class, 'showPackage'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.package');
    Route::put('/admin/bank-soal/paket/{exam}', [TeacherQuestionBankController::class, 'updatePackage'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.package.update');
    Route::delete('/admin/bank-soal/paket/{exam}', [TeacherQuestionBankController::class, 'destroyPackage'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.package.destroy');
    Route::post('/admin/bank-soal/paket/{exam}/soal', [TeacherQuestionBankController::class, 'storePackageQuestion'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.package.questions.store');
    Route::put('/admin/bank-soal/paket/{exam}/soal/{question}', [TeacherQuestionBankController::class, 'updatePackageQuestion'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.package.questions.update');
    Route::delete('/admin/bank-soal/{question}', [TeacherQuestionBankController::class, 'destroy'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.destroy');
    Route::get('/admin/bank-soal/template/pilihan-ganda', [TeacherQuestionBankController::class, 'downloadMultipleChoiceTemplate'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.template.pg');
    Route::get('/admin/bank-soal/template/esai', [TeacherQuestionBankController::class, 'downloadEssayTemplate'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.template.essay');
    Route::get('/admin/bank-soal/template/gabungan', [TeacherQuestionBankController::class, 'downloadCombinedTemplate'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.template.combined');
    Route::get('/admin/bank-soal/template/contoh-dokumen', [TeacherQuestionBankController::class, 'downloadPdfExample'])
        ->middleware('role:admin_remote')
        ->name('admin.question-bank.template.pdf');

    Route::get('/guru', [PortalDashboardController::class, 'teacher'])
        ->middleware('role:guru')
        ->name('teacher.dashboard');
    Route::get('/guru/mata-pelajaran', [TeacherSubjectController::class, 'index'])
        ->middleware('role:guru')
        ->name('teacher.subjects');
    Route::post('/guru/mata-pelajaran', [TeacherSubjectController::class, 'store'])
        ->middleware('role:guru')
        ->name('teacher.subjects.store');
    Route::get('/guru/mata-pelajaran/{subject}', [TeacherSubjectController::class, 'show'])
        ->middleware('role:guru')
        ->name('teacher.subjects.show');
    Route::put('/guru/mata-pelajaran/{subject}', [TeacherSubjectController::class, 'update'])
        ->middleware('role:guru')
        ->name('teacher.subjects.update');
    Route::delete('/guru/mata-pelajaran/{subject}', [TeacherSubjectController::class, 'destroy'])
        ->middleware('role:guru')
        ->name('teacher.subjects.destroy');
    Route::get('/guru/mata-pelajaran/{subject}/siswa-tersedia', [TeacherSubjectController::class, 'availableStudents'])
        ->middleware('role:guru')
        ->name('teacher.subjects.students.available');
    Route::post('/guru/mata-pelajaran/{subject}/siswa', [TeacherSubjectController::class, 'enrollStudent'])
        ->middleware('role:guru')
        ->name('teacher.subjects.students.store');
    Route::delete('/guru/mata-pelajaran/{subject}/siswa/{student}', [TeacherSubjectController::class, 'removeStudent'])
        ->middleware('role:guru')
        ->name('teacher.subjects.students.destroy');
    Route::get('/guru/mata-pelajaran/{subject}/materi', [TeacherSubjectMaterialController::class, 'index'])
        ->middleware('role:guru')
        ->name('teacher.subjects.materials');
    Route::post('/guru/mata-pelajaran/{subject}/materi', [TeacherSubjectMaterialController::class, 'store'])
        ->middleware('role:guru')
        ->name('teacher.subjects.materials.store');
    Route::delete('/guru/mata-pelajaran/{subject}/materi/{material}', [TeacherSubjectMaterialController::class, 'destroy'])
        ->middleware('role:guru')
        ->name('teacher.subjects.materials.destroy');
    Route::get('/guru/bank-soal', [TeacherQuestionBankController::class, 'index'])
        ->middleware('role:guru')
        ->name('teacher.question-bank');
    Route::get('/guru/bank-soal/buat', [TeacherQuestionBankController::class, 'create'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.create');
    Route::post('/guru/bank-soal/buat', [TeacherQuestionBankController::class, 'storeManual'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.store-manual');
    Route::post('/guru/bank-soal/impor', [TeacherQuestionBankController::class, 'import'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.import');
    Route::get('/guru/bank-soal/paket/{exam}', [TeacherQuestionBankController::class, 'showPackage'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.package');
    Route::put('/guru/bank-soal/paket/{exam}', [TeacherQuestionBankController::class, 'updatePackage'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.package.update');
    Route::delete('/guru/bank-soal/paket/{exam}', [TeacherQuestionBankController::class, 'destroyPackage'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.package.destroy');
    Route::post('/guru/bank-soal/paket/{exam}/soal', [TeacherQuestionBankController::class, 'storePackageQuestion'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.package.questions.store');
    Route::put('/guru/bank-soal/paket/{exam}/soal/{question}', [TeacherQuestionBankController::class, 'updatePackageQuestion'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.package.questions.update');
    Route::delete('/guru/bank-soal/{question}', [TeacherQuestionBankController::class, 'destroy'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.destroy');
    Route::get('/guru/bank-soal/unduh-template/pilihan-ganda', [TeacherQuestionBankController::class, 'downloadMultipleChoiceTemplate'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.template.pg');
    Route::get('/guru/bank-soal/unduh-template/esai', [TeacherQuestionBankController::class, 'downloadEssayTemplate'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.template.essay');
    Route::get('/guru/bank-soal/unduh-template/gabungan', [TeacherQuestionBankController::class, 'downloadCombinedTemplate'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.template.combined');
    Route::get('/guru/bank-soal/unduh-template/contoh-dokumen', [TeacherQuestionBankController::class, 'downloadPdfExample'])
        ->middleware('role:guru')
        ->name('teacher.question-bank.template.pdf');
    Route::get('/guru/sesi-ujian', [TeacherExamController::class, 'index'])
        ->middleware('role:guru')->name('teacher.exam-sessions');
    Route::get('/guru/sesi-ujian/buat', [TeacherExamController::class, 'create'])
        ->middleware('role:guru')->name('teacher.exam-sessions.create');
    Route::post('/guru/sesi-ujian', [TeacherExamController::class, 'store'])
        ->middleware('role:guru')->name('teacher.exam-sessions.store');
    Route::patch('/guru/sesi-ujian/{exam}/toggle-publish', [TeacherExamController::class, 'togglePublish'])
        ->middleware('role:guru')->name('teacher.exam-sessions.toggle-publish');
    Route::delete('/guru/sesi-ujian/{exam}', [TeacherExamController::class, 'destroy'])
        ->middleware('role:guru')->name('teacher.exam-sessions.destroy');
    Route::patch('/guru/sesi-ujian/{exam}/arsip', [TeacherExamController::class, 'archive'])
        ->middleware('role:guru')->name('teacher.exam-sessions.archive');
    Route::patch('/guru/sesi-ujian/{exam}/pulihkan', [TeacherExamController::class, 'restore'])
        ->middleware('role:guru')->name('teacher.exam-sessions.restore');
    Route::get('/guru/pemantauan', [TeacherMonitoringController::class, 'index'])
        ->middleware('role:guru')->name('teacher.monitoring');
    Route::post('/guru/pemantauan/pengerjaan/{attempt}/buka-akses', [TeacherMonitoringController::class, 'restoreAccess'])
        ->middleware('role:guru')->name('teacher.monitoring.restore-access');
    Route::get('/guru/hasil-penilaian', [TeacherResultsController::class, 'index'])
        ->middleware('role:guru')->name('teacher.results');
    Route::get('/guru/hasil-penilaian/pengerjaan/{attempt}', [TeacherResultsController::class, 'show'])
        ->middleware('role:guru')->name('teacher.results.review');
    Route::post('/guru/hasil-penilaian/pengerjaan/{attempt}/nilai', [TeacherResultsController::class, 'grade'])
        ->middleware('role:guru')->name('teacher.results.grade');

    Route::get('/siswa', [PortalDashboardController::class, 'student'])
        ->middleware('role:siswa')
        ->name('student.dashboard');
    Route::get('/siswa/mata-pelajaran', [StudentSubjectController::class, 'index'])
        ->middleware('role:siswa')
        ->name('student.subjects');
    Route::get('/siswa/mata-pelajaran/{subject}', [StudentSubjectMaterialController::class, 'show'])
        ->middleware('role:siswa')
        ->name('student.subjects.show');
    Route::get('/siswa/ujian', [StudentExamController::class, 'index'])
        ->middleware('role:siswa')->name('student.exams');
    Route::get('/siswa/ujian/{exam}', [StudentExamController::class, 'show'])
        ->middleware('role:siswa')->name('student.exams.show');
    Route::post('/siswa/ujian/{exam}/mulai', [StudentExamController::class, 'start'])
        ->middleware('role:siswa')->name('student.exams.start');
    Route::post('/siswa/ujian/{exam}/autosave', [StudentExamController::class, 'autosave'])
        ->middleware(['role:siswa', 'throttle:120,1'])->name('student.exams.autosave');
    Route::post('/siswa/ujian/{exam}', [StudentExamController::class, 'submit'])
        ->middleware('role:siswa')->name('student.exams.submit');
    Route::post('/siswa/ujian/{exam}/log-pelanggaran', [StudentExamController::class, 'logViolation'])
        ->middleware('role:siswa')->name('student.exams.log-violation');
    Route::get('/siswa/hasil-belajar', [StudentResultsController::class, 'index'])
        ->middleware('role:siswa')->name('student.results');

    Route::get('/akun', [PortalProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/akun', [PortalProfileController::class, 'update'])->name('profile.update');
    Route::get('/mata-pelajaran/{subject}/materi/{material}/unduh', SubjectMaterialDownloadController::class)
        ->name('subject-materials.download');
    Route::get('/mata-pelajaran/{subject}/materi/{material}/pratinjau', [SubjectMaterialDownloadController::class, 'preview'])
        ->name('subject-materials.preview');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
