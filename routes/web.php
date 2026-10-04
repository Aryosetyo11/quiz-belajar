<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PortalDashboardController;
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
    Route::get('/siswa', [PortalDashboardController::class, 'student'])
        ->middleware('role:siswa')
        ->name('student.dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
