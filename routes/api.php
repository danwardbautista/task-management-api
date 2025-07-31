<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth routes group
Route::controller(AuthController::class)
    ->prefix('auth')
    ->name('auth.')
    ->group(function () {
        // Public auth
        Route::post('/register', 'register')->name('register'); //decide later regarding rate limit
        Route::post('/login', 'login')->name('login');

        // Authenticated routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', 'logout')->name('logout');
            Route::get('/user', 'user')->name('user');
        });
    });

// Task routes group, added name prefix as well
Route::controller(TaskController::class)
    ->middleware('auth:sanctum')
    ->prefix('tasks')
    ->name('tasks.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{task}', 'show')->name('show');
        Route::put('/{task}', 'update')->name('update');
        Route::delete('/{task}', 'destroy')->name('destroy');
    });

// Subtask routes group
Route::controller(SubtaskController::class)
    ->middleware('auth:sanctum')
    ->prefix('tasks/{task}/subtasks')
    ->name('subtasks.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{subtask}', 'show')->name('show');
        Route::put('/{subtask}', 'update')->name('update');
        Route::delete('/{subtask}', 'destroy')->name('destroy');
    });

// Image route
Route::controller(ImageController::class)
    ->middleware('auth:sanctum')
    ->prefix('images')
    ->name('images.')
    ->group(function () {
        Route::get('/{filename}', 'show')->name('show');
    });
