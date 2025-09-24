<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// HOME PAGE
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// AUTENTICADO
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
