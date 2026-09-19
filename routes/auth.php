<?php

use App\Http\Controllers\Auth\ConfirmationController;
use App\Http\Controllers\Auth\GoogleLoginController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\VerificationController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('login/google', [GoogleLoginController::class, 'redirect'])->middleware('throttle:10,1')->name('login.google');
    Route::get('login/google/callback', [GoogleLoginController::class, 'callback'])->middleware('throttle:10,1')->name('login.google.callback');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [VerificationController::class, 'notice'])->name('verification.notice');
    Route::post('verify-email', [VerificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.store');
    Route::post('verify-whatsapp', [VerificationController::class, 'verifyWhatsApp'])->middleware('throttle:10,1')->name('verification.whatsapp');
    Route::post('verify-whatsapp/resend', [VerificationController::class, 'resendWhatsApp'])->middleware('throttle:6,1')->name('verification.whatsapp.resend');
    Route::get('verify-email/{id}/{hash}', [VerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::get('confirm-password', [ConfirmationController::class, 'create'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmationController::class, 'store'])->name('confirmation.store');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
});
