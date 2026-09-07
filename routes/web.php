<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PromptAnalyzerController;

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
});

// Route::get('/prompts', function () {
//     return Inertia::render('Prompts');
// })->middleware(['auth', 'verified'])->name('prompts.index');

Route::middleware('auth')->group(function () {
    Route::get('/prompts', [PromptAnalyzerController::class, 'index'])
        ->name('prompts.index');
    Route::post('/prompts', [PromptAnalyzerController::class, 'store'])
        ->name('prompts.store');
    Route::get('/prompts/{contentPrompt}/status', [PromptAnalyzerController::class, 'status'])
        ->name('prompts.status');
});

Route::middleware('auth')->group(function () {
    Route::get('/content-prompts/', [PromptAnalyzerController::class, 'analyze'])
        ->name('content-prompts.index');
});
require __DIR__.'/auth.php';
