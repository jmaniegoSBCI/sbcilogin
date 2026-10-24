<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\ProfileSetupController;
use App\Http\Controllers\StudentAuthController;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\TrackAuthenticatedActivity;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [StudentAuthController::class, 'create'])->name('login');
    Route::post('/login', [StudentAuthController::class, 'store'])->name('login.store');
    Route::get('/register', [StudentAuthController::class, 'register'])->name('register');
    Route::post('/register', [StudentAuthController::class, 'createAccount'])->name('register.store');

});

Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'store'])->name('admin.login.store');

Route::middleware(['auth', TrackAuthenticatedActivity::class])->group(function (): void {
    Route::post('/logout', [StudentAuthController::class, 'destroy'])->name('logout');
    Route::get('/continue', [StudentAuthController::class, 'continue'])->name('continue');

    Route::get('/profile/setup', [ProfileSetupController::class, 'edit'])->name('profile.setup');
    Route::post('/profile/setup', [ProfileSetupController::class, 'update'])->name('profile.setup.update');

    Route::middleware(EnsureUserIsAdmin::class)->prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/accounts/{user}/archive', [AdminDashboardController::class, 'archive'])->name('accounts.archive');
        Route::patch('/accounts/{user}/restore', [AdminDashboardController::class, 'restore'])->name('accounts.restore');
    });

    Route::get('/student/dashboard', function () {
        return view('student.dashboard');
    })->middleware('student.profile')->name('student.dashboard');
});
