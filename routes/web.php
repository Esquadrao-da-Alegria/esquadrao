<?php

use App\Http\Controllers\Web\EventoController;
use App\Http\Controllers\Web\EventoFinalizacaoController;
use App\Http\Controllers\Web\EventoInscricaoController;
use App\Http\Controllers\Web\EventoPresencaController;
use App\Http\Controllers\Web\HospitalController;
use App\Http\Controllers\Web\MeuEventoController;
use App\Http\Controllers\Web\Json\CidadeController;
use App\Http\Controllers\Web\OndeAtuamosController;
use App\Http\Controllers\Web\PatrocinadorController;
use App\Http\Controllers\Web\VoluntarioController;
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


    Route::get('/meus-eventos', [MeuEventoController::class, 'index'])->name('meus-eventos.index');
    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');

    // ADMINISTRADOR
    Route::middleware(['administrador'])->group(function () {
        Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create');
        Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
        Route::get('/eventos/{evento}/edit', [EventoController::class, 'edit'])->name('eventos.edit');
        Route::put('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
        Route::post('/eventos/{evento}/cancelar', [EventoController::class, 'cancelar'])->name('eventos.cancelar');
        Route::delete('/eventos/{evento}', [EventoController::class, 'destroy'])->name('eventos.destroy');

        // VOLUNTARIOS
        Route::resource('/voluntarios', VoluntarioController::class)
            ->parameters(['voluntarios' => 'voluntario'])
            ->except(['show']);

        // Hospitais
        Route::resource('/hospitais', HospitalController::class)->parameters(['hospitais' => 'hospital']);

        // patrocinadores
        Route::resource('/patrocinadores', PatrocinadorController::class)->parameters(['patrocinadores' => 'patrocinador']);
    });

    Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');
    Route::post('/eventos/{evento}/inscricao', [EventoInscricaoController::class, 'store'])->name('eventos.inscricao.store');
    Route::delete('/eventos/{evento}/inscricao', [EventoInscricaoController::class, 'destroy'])->name('eventos.inscricao.destroy');
    Route::post('/eventos/{evento}/finalizar', [EventoFinalizacaoController::class, 'store'])->name('eventos.finalizar');
    Route::put('/eventos/{evento}/presencas', [EventoPresencaController::class, 'update'])->name('eventos.presencas.update');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
