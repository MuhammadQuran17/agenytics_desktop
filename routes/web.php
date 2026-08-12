<?php

use App\Http\Controllers\AiChat\AiChatController;
use App\Http\Controllers\AiChat\Api\ChatStatusPollingController;
use App\Http\Controllers\AiChat\Api\SendMessageToAiController;
use App\Http\Controllers\ApiKey\ApiKeyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('/privacy-policy', function () {
    return view('privacy-policy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return view('terms-of-service');
})->name('terms-of-service');

Route::get('/documentation', function () {
    return view('documentation');
})->name('documentation');

Route::middleware('auth')->group(function () {
    // Chat Management
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/create', [AiChatController::class, 'create'])->name('create');
        Route::post('/send', SendMessageToAiController::class)->name('send');
        Route::post('/status', ChatStatusPollingController::class)->name('status');
        Route::delete('/{userChat}', [AiChatController::class, 'destroy'])->name('destroy');
        Route::get('/{userChat?}', [AiChatController::class, 'index'])->name('index');
    });

    Route::redirect('dashboard', 'chat')->name('dashboard');

    // API Key Management
    Route::prefix('api-keys')->name('api-keys.')->group(function () {
        Route::post('/check', [ApiKeyController::class, 'checkIfExists'])->name('checkIfExists');
        Route::post('/', [ApiKeyController::class, 'store'])->name('store');
        Route::delete('/', [ApiKeyController::class, 'clear'])->name('clear');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
