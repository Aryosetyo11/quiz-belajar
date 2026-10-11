<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PortalDashboardController;
use App\Http\Controllers\PortalProfileController;
use App\Http\Controllers\StudentSubjectController;
use App\Http\Controllers\StudentSubjectMaterialController;
use App\Http\Controllers\SubjectMaterialDownloadController;
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
});

Route::middleware('auth')->group(function (): void {
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
    Route::view('/guru/sesi-ujian', 'portal.coming-soon', [
        'pageTitle' => 'Sesi Ujian',
        'pageDescription' => 'Atur jadwal, durasi, peserta, dan aturan pengerjaan ujian.',
    ])->middleware('role:guru')->name('teacher.exam-sessions');
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
    Route::view('/siswa/ujian', 'portal.coming-soon', [
        'pageTitle' => 'Ujian Saya',
        'pageDescription' => 'Lihat jadwal ujian dan petunjuk pengerjaan yang diberikan pengajar.',
    ])->middleware('role:siswa')->name('student.exams');
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
