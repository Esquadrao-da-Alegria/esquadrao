<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\HospitalController;
use App\Http\Controllers\Web\OndeAtuamosController;

// HOME PAGE
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// Hospitais
Route::get('/onde-atuamos', [OndeAtuamosController::class, 'index'])->name('onde_atuamos.index');

// Conheça
Route::get('/conheça', function () {
    return Inertia::render('Conheca/Index');
})->name('conheca.index');

// Conheça
Route::get('/doacoes', function () {
    return Inertia::render('Doacao/Index');
})->name('doacoes.index');

// Fale Conosco
Route::get('/fale-conosco', function () {
    return Inertia::render('FaleConosco/Index');
})->name('fale_conosco.index');

// Hospitais
Route::resource('/hospitais', HospitalController::class)->parameters(['hospitais'=>'hospital']);

// AUTENTICADO
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
