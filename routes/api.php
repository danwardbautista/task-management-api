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

// Auth routes group
Route::controller(AuthController::class)
    ->prefix('auth')
    ->name('auth.')
    ->group(function () {
        // Public auth with strict rate limiting
        Route::post('/register', 'register')->name('register')->middleware('throttle:5,1'); // 5 attempts per minute
        Route::post('/login', 'login')->name('login')->middleware('throttle:10,1'); // 10 attempts per minute

        // Authenticated routes with standard rate limiting
        Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
            Route::post('/logout', 'logout')->name('logout');
            Route::get('/user', 'user')->name('user');
        });
    });

// Task routes group, added name prefix as well
Route::controller(TaskController::class)
    ->middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('tasks')
    ->name('tasks.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::get('/{task}', 'show')->name('show');
        Route::put('/{task}', 'update')->name('update');
        Route::delete('/{task}', 'destroy')->name('destroy');
        Route::patch('/{task}/restore', 'restore')->name('restore');
        Route::delete('/{task}/force-delete', 'forceDelete')->name('force_delete');
    });

// Subtask routes group
Route::controller(SubtaskController::class)
    ->middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('tasks/{task}/subtasks')
    ->name('subtasks.')
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/trashed', 'trashed')->name('trashed');
        Route::get('/{subtask}', 'show')->name('show');
        Route::put('/{subtask}', 'update')->name('update');
        Route::delete('/{subtask}', 'destroy')->name('destroy');
        Route::patch('/{subtask}/restore', 'restore')->name('restore');
        Route::delete('/{subtask}/force-delete', 'forceDelete')->name('force_delete');
    });

// Image route
Route::controller(ImageController::class)
    ->middleware(['auth:sanctum', 'throttle:60,1'])
    ->prefix('images')
    ->name('images.')
    ->group(function () {
        Route::get('/{filename}', 'show')->name('show');
    });
