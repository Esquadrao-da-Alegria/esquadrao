<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// HOME PAGE
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Hospitais
Route::get('/hospitais', function () {
    return Inertia::render('Hospitais/Index');
})->name('hospitais.index');

// Conheça
Route::get('/conheça', function () {
    return Inertia::render('Conheca/Index');
})->name('conheca.index');

// AUTENTICADO
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
