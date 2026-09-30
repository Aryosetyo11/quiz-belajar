<?php

use App\Http\Controllers\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

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
    Route::get('/guru', [AuthenticatedSessionController::class, 'teacherDashboard'])
        ->middleware('role:guru')
        ->name('teacher.dashboard');
    Route::get('/siswa', [AuthenticatedSessionController::class, 'studentDashboard'])
        ->middleware('role:siswa')
        ->name('student.dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
