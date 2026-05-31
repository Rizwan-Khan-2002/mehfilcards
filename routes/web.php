<?php

use App\Http\Controllers\InvitationController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [InvitationController::class, 'home'])->name('home');
Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');
Route::get('/demo', [InvitationController::class, 'demo'])->name('demo');
Route::post('/subscribe', [InvitationController::class, 'subscribe'])->name('subscribe');

Route::get('/invite/{invitation}', [InvitationController::class, 'show'])->name('invite.show');
Route::get('/invite/{invitation}/download', [InvitationController::class, 'download'])->name('invite.download');
Route::get('/qr/{invitation}.png', [InvitationController::class, 'qr'])->name('invite.qr');
Route::get('/qr-preview.png', [InvitationController::class, 'previewQr'])->name('qr.preview');

Route::get('/scanner', [InvitationController::class, 'scanner'])->name('scanner');
Route::post('/verify', [InvitationController::class, 'verify'])->name('verify');

Route::get('/payments', [InvitationController::class, 'payments'])->name('payments');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [InvitationController::class, 'admin'])->name('admin');
    Route::post('/admin/categories', [InvitationController::class, 'storeCategory'])->name('admin.categories.store');
    Route::post('/admin/templates', [InvitationController::class, 'storeTemplate'])->name('admin.templates.store');
});

Route::get('/card-art/{slug}.svg', [InvitationController::class, 'cardArt'])->name('card.art');
