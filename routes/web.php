<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NoteController; // <-- THIS IS THE MAGIC LINE
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notes Routes
    Route::get('/my-journal', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/my-journal', [NoteController::class, 'store'])->name('notes.store');
    Route::put('/my-journal/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/my-journal/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Trash Routes
    Route::get('/my-journal/trash', [NoteController::class, 'trash'])->name('notes.trash');
    Route::put('/my-journal/{id}/restore', [NoteController::class, 'restore'])->name('notes.restore');
    Route::delete('/my-journal/{id}/force-delete', [NoteController::class, 'forceDelete'])->name('notes.forceDelete');
});

require __DIR__.'/auth.php';
