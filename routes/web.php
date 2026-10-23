<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Controllers\PortalProfileController;
use App\Http\Controllers\StudentExamController;
use App\Http\Controllers\StudentSubjectController;
use App\Http\Controllers\StudentSubjectMaterialController;
use App\Http\Controllers\SubjectMaterialDownloadController;
use App\Http\Controllers\TeacherExamController;
use App\Http\Controllers\TeacherSubjectController;
use App\Http\Controllers\TeacherSubjectMaterialController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home.index')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'showRoleSelection'])->name('login');
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
});

Route::middleware('auth')->group(function (): void {
    Route::get('/admin', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin_remote')->name('remote.dashboard');
    Route::get('/yayasan', [AdminDashboardController::class, 'index'])
        ->middleware('role:admin_yayasan')->name('foundation.dashboard');
    Route::get('/admin/pengguna', [AdminUserController::class, 'index'])
        ->middleware('role:admin_remote')
        ->name('admin.users');
    Route::post('/admin/pengguna', [AdminUserController::class, 'store'])
        ->middleware('role:admin_remote')
        ->name('admin.users.store');
    Route::put('/admin/pengguna/{user}', [AdminUserController::class, 'update'])
        ->middleware('role:admin_remote')
        ->name('admin.users.update');
    Route::delete('/admin/pengguna/{user}', [AdminUserController::class, 'destroy'])
        ->middleware('role:admin_remote')
        ->name('admin.users.destroy');

    Route::get('/guru', [PortalDashboardController::class, 'teacher'])
        ->middleware('role:guru')
        ->name('teacher.dashboard');
    Route::get('/guru/mata-pelajaran', [TeacherSubjectController::class, 'index'])
        ->middleware('role:guru')
        ->name('teacher.subjects');
    Route::post('/guru/mata-pelajaran', [TeacherSubjectController::class, 'store'])
        ->middleware('role:guru')
        ->name('teacher.subjects.store');
    Route::put('/guru/mata-pelajaran/{subject}', [TeacherSubjectController::class, 'update'])
        ->middleware('role:guru')
        ->name('teacher.subjects.update');
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
    Route::view('/guru/bank-soal', 'portal.coming-soon', [
        'pageTitle' => 'Bank Soal',
        'pageDescription' => 'Kelola soal pilihan ganda dan esai sebelum digunakan dalam sesi evaluasi.',
    ])->middleware('role:guru')->name('teacher.question-bank');
    Route::get('/guru/sesi-ujian', [TeacherExamController::class, 'index'])
        ->middleware('role:guru')->name('teacher.exam-sessions');
    Route::get('/guru/sesi-ujian/buat', [TeacherExamController::class, 'create'])
        ->middleware('role:guru')->name('teacher.exam-sessions.create');
    Route::post('/guru/sesi-ujian/impor', [TeacherExamController::class, 'import'])
        ->middleware('role:guru')->name('teacher.exam-sessions.import');
    Route::post('/guru/sesi-ujian', [TeacherExamController::class, 'store'])
        ->middleware('role:guru')->name('teacher.exam-sessions.store');
    Route::view('/guru/pemantauan', 'portal.coming-soon', [
        'pageTitle' => 'Pemantauan',
        'pageDescription' => 'Pantau progres peserta dan catatan aktivitas selama sesi ujian berlangsung.',
    ])->middleware('role:guru')->name('teacher.monitoring');
    Route::view('/guru/hasil-penilaian', 'portal.coming-soon', [
        'pageTitle' => 'Hasil Penilaian',
        'pageDescription' => 'Tinjau nilai, periksa jawaban esai, dan siapkan rekap hasil evaluasi.',
    ])->middleware('role:guru')->name('teacher.results');

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
    Route::post('/siswa/ujian/{exam}', [StudentExamController::class, 'submit'])
        ->middleware('role:siswa')->name('student.exams.submit');
    Route::view('/siswa/hasil-belajar', 'portal.coming-soon', [
        'pageTitle' => 'Hasil Belajar',
        'pageDescription' => 'Lihat nilai dan hasil evaluasi setelah diumumkan oleh pengajar.',
    ])->middleware('role:siswa')->name('student.results');

    Route::get('/akun', [PortalProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/akun', [PortalProfileController::class, 'update'])->name('profile.update');
    Route::get('/mata-pelajaran/{subject}/materi/{material}/unduh', SubjectMaterialDownloadController::class)
        ->name('subject-materials.download');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
