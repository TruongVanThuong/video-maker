<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\SettingController;


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

Route::get('/categories', function () {
    return Inertia::render('Categories/Index', [
        'categories' => [
            ['id' => 1, 'name' => 'Category 1'],
            ['id' => 2, 'name' => 'Category 2'],
            ['id' => 3, 'name' => 'Category 3'],
        ],
    ]);
})->middleware(['auth', 'verified'])->name('categories');
Route::get('/categories/create', function () {
    return Inertia::render('Categories/Create');
})->middleware(['auth', 'verified'])->name('categories.create');
Route::get('/categories/{category}/edit', function ($category) {
    return Inertia::render('Categories/Edit', [
        'category' => ['id' => $category, 'name' => 'Category ' . $category],
    ]);
})->middleware(['auth', 'verified'])->name('categories.edit');
Route::put('/categories/{category}', function ($category) {
    return Inertia::render('Categories/Index', [
        'category' => ['id' => $category, 'name' => 'Category ' . $category],
    ]);
})->middleware(['auth', 'verified'])->name('categories.update');

Route::get('/settings', [SettingController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('settings.index');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
