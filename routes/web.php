<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('tasks.index');
});

// group the routes for better organization
Route::group(['prefix' => 'projects', 'as' => 'projects.'], function () {
    Route::get('/create', [ProjectController::class, 'create'])->name('create');
    Route::post('', [ProjectController::class, 'store'])->name('store');
});

// Task routes
Route::resource('tasks', TaskController::class)->except(['show', 'create']);
Route::post('/tasks/reorder', [TaskController::class, 'reorder'])->name('tasks.reorder');
