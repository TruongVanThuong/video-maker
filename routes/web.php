<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PromptAnalyzerController;
use App\Http\Controllers\PromptTemplateController;

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
    Route::patch('/prompts/{contentPrompt}', [PromptAnalyzerController::class, 'update'])
        ->name('prompts.update');
    Route::post('/prompts/{contentPrompt}/refine', [PromptAnalyzerController::class, 'refine'])
        ->name('prompts.refine');
});

Route::middleware('auth')->group(function () {
    Route::get('/prompt-templates', [PromptTemplateController::class, 'index'])
        ->name('prompt-templates.index');
    Route::post('/prompt-templates', [PromptTemplateController::class, 'store'])
        ->name('prompt-templates.store');
    Route::put('/prompt-templates/{promptTemplate}', [PromptTemplateController::class, 'update'])
        ->name('prompt-templates.update');
    Route::post('/prompt-templates/{promptTemplate}/duplicate', [PromptTemplateController::class, 'duplicate'])
        ->name('prompt-templates.duplicate');
    Route::delete('/prompt-templates/{promptTemplate}', [PromptTemplateController::class, 'destroy'])
        ->name('prompt-templates.destroy');
    Route::patch('/prompt-templates/{promptTemplate}/toggle-status', [PromptTemplateController::class, 'toggleStatus'])
        ->name('prompt-templates.toggle-status');
    Route::post('/prompt-templates/test', [PromptTemplateController::class, 'test'])
        ->name('prompt-templates.test');
});
require __DIR__.'/auth.php';
