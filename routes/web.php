<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/account', AccountController::class)->name('account');
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'create'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store'])->name('password.confirm.store');

    Route::view('/access-check/tourist', 'account.access-check')->middleware('role:tourist')->defaults('role', 'Tourist');
    Route::view('/access-check/tour-guide', 'account.access-check')->middleware('role:tour_guide')->defaults('role', 'Tour Guide');
    Route::view('/access-check/service-provider', 'account.access-check')->middleware('role:service_provider')->defaults('role', 'Service Provider');
    Route::view('/access-check/bureau', 'account.access-check')->middleware('role:tourism_bureau_officer')->defaults('role', 'Tourism Bureau Officer');
    Route::view('/access-check/administrator', 'account.access-check')->middleware('role:administrator')->defaults('role', 'Administrator');
});
