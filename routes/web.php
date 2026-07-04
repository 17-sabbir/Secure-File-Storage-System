<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CryptoController;
use App\Http\Controllers\PasswordStrengthController;
use App\Http\Controllers\ChatController;

// ── Welcome ──────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ── Auth ─────────────────────────────────────────────────────────────────────
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',    [AuthController::class, 'login']);

Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');

// ── Two-Factor Authentication ─────────────────────────────────────────────────
Route::get('/verify-2fa',  [AuthController::class, 'showVerify'])->name('2fa.verify');
Route::post('/verify-2fa', [AuthController::class, 'verify'])->name('2fa.verify.submit');
Route::post('/verify-2fa/resend', [AuthController::class, 'resend'])->name('2fa.resend');

// ── Password Strength Checker (public — used live on the register form) ─────
Route::post('/password-strength', [PasswordStrengthController::class, 'check'])->name('password.strength');

// ── Protected (requires login) ────────────────────────────────────────────────
Route::middleware(\App\Http\Middleware\AuthMiddleware::class)->group(function () {
    Route::get('/dashboard',             [CryptoController::class, 'dashboard'])->name('dashboard');
    Route::post('/encrypt',              [CryptoController::class, 'encrypt'])->name('encrypt');
    Route::post('/decrypt',              [CryptoController::class, 'decrypt'])->name('decrypt');
    Route::get('/download/encrypted/{file}', [CryptoController::class, 'downloadEncrypted'])->name('download.encrypted');
    Route::get('/download/decrypted/{file}', [CryptoController::class, 'downloadDecrypted'])->name('download.decrypted');
    Route::delete('/file/encrypted/{file}',  [CryptoController::class, 'deleteEncrypted'])->name('file.delete.encrypted');
    Route::delete('/file/decrypted/{file}',  [CryptoController::class, 'deleteDecrypted'])->name('file.delete.decrypted');

    // Sharing an encrypted file with another registered user
    Route::post('/file/share/{file}',        [CryptoController::class, 'share'])->name('file.share');
    Route::delete('/file/share/{share}',     [CryptoController::class, 'unshare'])->name('file.share.revoke');

    // Opening a decrypted file inline in the browser instead of forcing a download
    Route::get('/open/decrypted/{file}',     [CryptoController::class, 'openDecrypted'])->name('open.decrypted');

    // ── Encrypted Chat ────────────────────────────────────────────────────────
    Route::get('/chat',                      [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/public-key',          [ChatController::class, 'storePublicKey'])->name('chat.public-key');
    Route::get('/chat/public-keys',          [ChatController::class, 'publicKeys'])->name('chat.public-keys');
    Route::get('/chat/{user}/messages',      [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/{user}/messages',     [ChatController::class, 'send'])->name('chat.send');
});