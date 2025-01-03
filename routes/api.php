<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChatLogController;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
])->group(function () {

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/logout', 'logout')->middleware('auth:sanctum');
        Route::get('/user', 'user')->middleware('auth:sanctum');
    });
    Route::get('/documentation', function () {
        return view('swagger.index');
    });
    Route::middleware('auth:sanctum')->group(function () {
        Route::controller(UserController::class)->group(function () {
            Route::get('/users', 'index');
            Route::get('/users/{id}', 'show');
            Route::post('/users', 'store');
            Route::put('/users/{id}', 'update');
            Route::delete('/users/{id}', 'destroy');

            // Chat API
            Route::post('/chat', [ChatController::class, 'handleChat']);

            // Log Management (Restricted to specific roles)
            Route::middleware('can:viewAny,App\Models\ChatLog')->group(function () {
                Route::get('/chat-logs', [ChatLogController::class, 'index']);
                Route::delete('/chat-logs/{id}', [ChatLogController::class, 'destroy']);
            });
        });
    });

});
