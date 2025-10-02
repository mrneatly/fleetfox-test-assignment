<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TaskCategoryController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('task-categories', TaskCategoryController::class)->parameters([
        'task-categories' => 'taskCategory',
    ]);

    // Tasks CRUD
    Route::resource('tasks', TaskController::class)->except('show');
    Route::patch('tasks/{task}/done', [TaskController::class, 'setDone'])->name('tasks.done');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
