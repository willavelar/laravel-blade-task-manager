<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('tasks.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('tasks.index'))->name('dashboard');

    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])
        ->name('tasks.toggle');

    Route::resource('categories', CategoryController::class);
});

require __DIR__.'/auth.php';
