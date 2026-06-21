<?php

use App\Http\Controllers\Web\HospitalController;
use App\Http\Controllers\Web\Json\CidadeController;
use App\Http\Controllers\Web\OndeAtuamosController;
use App\Http\Controllers\Web\PatrocinadorController;
use App\Http\Controllers\Web\VoluntarioController;
use App\Http\Controllers\Web\EventoController;
use App\Models\Patrocinador;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// URLs do site estático antigo (index.html, etc.)
Route::redirect('/index.html', '/', 301);

// HOME PAGE
Route::get('/', function () {
    return Inertia::render('Home', [
        'patrocinadores' => Patrocinador::where('ativo', true)
            ->orderBy('ordem_exibicao')
            ->get(),
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

// AUTENTICADO
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Listas JSON
    ROUTE::prefix('json')->name('json.')->group(function () {

        Route::get('cidades', [CidadeController::class, 'index'])->name('json.cidades.index');
    });

    // Eventos — rota estática ANTES do resource para evitar conflito de parâmetros
    Route::get('/eventos/dashboard', [EventoController::class, 'dashboard'])->name('eventos.dashboard');

    // Eventos — resource padrão
    Route::resource('/eventos', EventoController::class)->parameters(['eventos' => 'evento']);

    // Eventos — ações extras (fora do CRUD padrão)
    Route::post('/eventos/{evento}/inscrever', [EventoController::class, 'inscrever'])->name('eventos.inscrever');
    Route::delete('/eventos/{evento}/inscricao', [EventoController::class, 'cancelarInscricao'])->name('eventos.cancelar-inscricao');
    Route::get('/eventos/{evento}/finalizar', [EventoController::class, 'paginaFinalizar'])->name('eventos.pagina-finalizar');
    Route::post('/eventos/{evento}/finalizar', [EventoController::class, 'finalizar'])->name('eventos.finalizar');
    Route::post('/eventos/{evento}/cancelar', [EventoController::class, 'cancelar'])->name('eventos.cancelar');

    // ADMINISTRADOR
    Route::middleware(['administrador'])->group(function () {

        // VOLUNTARIOS
        Route::resource('/voluntarios', VoluntarioController::class)
            ->parameters(['voluntarios' => 'voluntario'])
            ->except(['show']);

        // Hospitais
        Route::resource('/hospitais', HospitalController::class)->parameters(['hospitais' => 'hospital']);

        // patrocinadores
        Route::resource('/patrocinadores', PatrocinadorController::class)->parameters(['patrocinadores' => 'patrocinador']);
    });
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
