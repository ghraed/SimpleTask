<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('tasks.index');
});

// Project routes
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');

// Task routes
Route::resource('tasks', TaskController::class)->except(['show', 'create']);
Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
