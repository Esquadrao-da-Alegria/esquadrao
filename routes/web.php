<?php

use App\Http\Controllers\Web\EventoController;
use App\Http\Controllers\Web\EventoInscricaoController;
use App\Http\Controllers\Web\HospitalController;
use App\Http\Controllers\Web\ConviteCadastroController;
use App\Http\Controllers\Web\Visita\Participante\VisitaParticipanteController;
use App\Http\Controllers\Web\VisitaController;
use App\Http\Controllers\Web\Json\CidadeController;
use App\Http\Controllers\Web\OndeAtuamosController;
use App\Http\Controllers\Web\PatrocinadorController;
use App\Http\Controllers\Web\VoluntarioController;
use App\Models\Patrocinador;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/convites/{token}', [ConviteCadastroController::class, 'show'])
    ->name('convites.show');

Route::post('/convites/{token}/concluir', [ConviteCadastroController::class, 'concluir'])
    ->name('convites.concluir');

// AUTENTICADO
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Listas JSON
    ROUTE::prefix('json')->name('json.')->group(function () {

        Route::get('cidades', [CidadeController::class, 'index'])->name('cidades.index');
    });

    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');
    Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');
    Route::post('/eventos/{evento}/inscricao', [EventoInscricaoController::class, 'store'])->name('eventos.inscricao.store');
    Route::delete('/eventos/{evento}/inscricao', [EventoInscricaoController::class, 'destroy'])->name('eventos.inscricao.destroy');

    // VISITAS
    Route::prefix('visitas')->name('visitas.')->group(function () {
        Route::get('/', [VisitaController::class, 'index'])->name('index');

        Route::post('{visita}/participantes', [VisitaParticipanteController::class, 'store'])
            ->name('participantes.store');

        Route::delete('{visita}/participantes/{participante}', [VisitaParticipanteController::class, 'destroy'])
            ->name('participantes.destroy');
    });

    // ADMINISTRADOR
    Route::middleware(['administrador'])->group(function () {

        Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create');
        Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
        Route::get('/eventos/{evento}/edit', [EventoController::class, 'edit'])->name('eventos.edit');
        Route::put('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
        Route::post('/eventos/{evento}/cancelar', [EventoController::class, 'cancelar'])->name('eventos.cancelar');

        // VOLUNTARIOS
        Route::post('/voluntarios/convite', [VoluntarioController::class, 'storeConvite'])
            ->name('voluntarios.convite.store');

        Route::post('/voluntarios/{voluntario}/reenviar-convite', [VoluntarioController::class, 'reenviarConvite'])
            ->name('voluntarios.convite.reenviar');

        Route::delete('/voluntarios/{voluntario}/convite', [VoluntarioController::class, 'cancelarConvite'])
            ->name('voluntarios.convite.cancelar');

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
