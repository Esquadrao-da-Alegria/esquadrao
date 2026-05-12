<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Models\Patrocinador;
use App\Http\Controllers\Web\HospitalController;
use App\Http\Controllers\Web\PatrocinadorController;
use App\Http\Controllers\Web\OndeAtuamosController;
use App\Http\Controllers\Web\Json\CidadeController;

// HOME PAGE
Route::get('/', function () {
    return Inertia::render('Home', [
        'patrocinadores' => Patrocinador::where('ativo', true)
                                        ->orderBy('ordem_exibicao')
                                        ->get()
    ]);
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
Route::resource('/hospitais', HospitalController::class)->parameters(['hospitais' => 'hospital']);

// patrocinadores
Route::resource('/patrocinadores', PatrocinadorController::class)->parameters(['patrocinadores' => 'patrocinador']);

// Listas JSON
ROUTE::prefix('json')->name('json.')->group(function () {

    Route::get('cidades', [CidadeController::class, 'index'])->name('json.cidades.index');
});

// AUTENTICADO
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
