<?php

use App\Http\Controllers\Web\EventoController;
use App\Http\Controllers\Web\Dashboard\Meu\Controller as MeuDashboardController;
use App\Http\Controllers\Web\Dashboard\Visita\Hospital\Controller as DashboardVisitaHospitalController;
use App\Http\Controllers\Web\Dashboard\Visita\Participante\Controller as DashboardVisitaParticipanteController;
use App\Http\Controllers\Web\EventoFinalizacaoController;
use App\Http\Controllers\Web\EventoInscricaoController;
use App\Http\Controllers\Web\EventoPresencaController;
use App\Http\Controllers\Web\Evento\Ajuste\Controller as EventoAjusteController;
use App\Http\Controllers\Web\HospitalController;
use App\Http\Controllers\Web\MeuEventoController;
use App\Http\Controllers\Web\ConviteCadastroController;
use App\Http\Controllers\Web\Visita\Participante\VisitaParticipanteController;
use App\Http\Controllers\Web\Visita\Ajuste\Controller as VisitaAjusteController;
use App\Http\Controllers\Web\Visita\Relatorio\VisitaRelatorioController;
use App\Http\Controllers\Web\Visita\VisitaController;
use App\Http\Controllers\Web\Json\CidadeController;
use App\Http\Controllers\Web\OndeAtuamosController;
use App\Http\Controllers\Web\PatrocinadorController;
use App\Http\Controllers\Web\VoluntarioController;
use App\Models\Patrocinador;
use App\Services\Dashboard\Permissao\Service as DashboardPermissaoService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::redirect('/index.html', '/', 301);

Route::get('/', function () {
    return Inertia::render('Home', [
        'patrocinadores' => Patrocinador::where('ativo', true)
            ->orderBy('ordem_exibicao')
            ->get(),
    ]);
})->name('home');

Route::get('/onde-atuamos', [OndeAtuamosController::class, 'index'])->name('onde_atuamos.index');

Route::get('/conheça', function () {
    return Inertia::render('Conheca/Index');
})->name('conheca.index');

Route::get('/doacoes', function () {
    return Inertia::render('Doacao/Index');
})->name('doacoes.index');

Route::get('/fale-conosco', function () {
    return Inertia::render('FaleConosco/Index');
})->name('fale_conosco.index');

Route::get('/convites/{token}', [ConviteCadastroController::class, 'show'])
    ->name('convites.show');

Route::post('/convites/{token}/concluir', [ConviteCadastroController::class, 'concluir'])
    ->name('convites.concluir');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('dashboards')->name('dashboards.')->group(function () {
        Route::get('meu', [MeuDashboardController::class, 'index'])
            ->middleware('can:'.DashboardPermissaoService::MEU_DASHBOARD)->name('meu');

        Route::get('visao-geral', function () {
            return Inertia::render('Dashboard/Gerencial', [
                'titulo' => 'Visão geral',
                'descricao' => 'Os indicadores gerais serão disponibilizados nesta área.',
            ]);
        })->middleware('can:'.DashboardPermissaoService::VISAO_GERAL)->name('visao-geral');

        Route::get('visitas-por-hospital', [DashboardVisitaHospitalController::class, 'index'])
            ->middleware('can:'.DashboardPermissaoService::VISITAS_POR_HOSPITAL)
            ->name('visitas-por-hospital');

        Route::get('visitas-por-participante', [DashboardVisitaParticipanteController::class, 'index'])
            ->middleware('can:'.DashboardPermissaoService::VISITAS_POR_PARTICIPANTE)
            ->name('visitas-por-participante');
        Route::get('visitas-por-participante/{voluntario}', [DashboardVisitaParticipanteController::class, 'show'])
            ->middleware('can:'.DashboardPermissaoService::VISITAS_POR_PARTICIPANTE)
            ->name('visitas-por-participante.show');
    });

    Route::get('ajuda', function () {
        return Inertia::render('Ajuda/Index');
    })->name('ajuda.index');

    ROUTE::prefix('json')->name('json.')->group(function () {
        Route::get('cidades', [CidadeController::class, 'index'])->name('cidades.index');
    });

    Route::get('/eventos', [EventoController::class, 'index'])->name('eventos.index');

    Route::middleware(['administrador'])->group(function () {
        Route::get('/eventos/create', [EventoController::class, 'create'])->name('eventos.create');
        Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
        Route::post('/eventos/{evento}/ajustes-participacao', [EventoAjusteController::class, 'store'])->name('eventos.ajustes-participacao.store');
    });

    Route::get('/meus-eventos', [MeuEventoController::class, 'index'])->name('meus-eventos.index');

    Route::get('/eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');
    Route::post('/eventos/{evento}/inscricao', [EventoInscricaoController::class, 'store'])->name('eventos.inscricao.store');
    Route::delete('/eventos/{evento}/inscricao', [EventoInscricaoController::class, 'destroy'])->name('eventos.inscricao.destroy');

    Route::prefix('visitas')->name('visitas.')->group(function () {
        Route::get('/', [VisitaController::class, 'index'])->name('index');
        Route::get('create', [VisitaController::class, 'create'])->name('create');
        Route::post('/', [VisitaController::class, 'store'])->name('store');
        Route::get('{visita}/edit', [VisitaController::class, 'edit'])->name('edit');
        Route::put('{visita}', [VisitaController::class, 'update'])->name('update');
        Route::post('{visita}/cancelar', [VisitaController::class, 'cancelar'])->name('cancelar');

        Route::middleware('administrador')->prefix('{visita}/ajustes-contabilizacao')->name('ajustes-contabilizacao.')->group(function () {
            Route::post('/', [VisitaAjusteController::class, 'store'])->name('store');
        });

        Route::prefix('{visita}/relatorios')->scopeBindings()->name('relatorios.')->group(function () {
            Route::get('/', [VisitaRelatorioController::class, 'index'])->name('index');
            Route::get('create', [VisitaRelatorioController::class, 'create'])->name('create');
            Route::post('/', [VisitaRelatorioController::class, 'store'])->name('store');
            Route::get('{relatorio}', [VisitaRelatorioController::class, 'show'])->name('show');
            Route::get('{relatorio}/edit', [VisitaRelatorioController::class, 'edit'])->name('edit');
            Route::put('{relatorio}', [VisitaRelatorioController::class, 'update'])->name('update');
            Route::get('{relatorio}/pdf', [VisitaRelatorioController::class, 'pdf'])->name('pdf');
        });

        Route::post('{visita}/participantes', [VisitaParticipanteController::class, 'store'])
            ->name('participantes.store');

        Route::delete('{visita}/participantes/{participante}', [VisitaParticipanteController::class, 'destroy'])
            ->name('participantes.destroy');
    });

    Route::middleware(['administrador'])->group(function () {
        Route::post('/eventos', [EventoController::class, 'store'])->name('eventos.store');
        Route::get('/eventos/{evento}/edit', [EventoController::class, 'edit'])->name('eventos.edit');
        Route::put('/eventos/{evento}', [EventoController::class, 'update'])->name('eventos.update');
        Route::post('/eventos/{evento}/cancelar', [EventoController::class, 'cancelar'])->name('eventos.cancelar');
        Route::delete('/eventos/{evento}', [EventoController::class, 'destroy'])->name('eventos.destroy');

        Route::post('/voluntarios/convite', [VoluntarioController::class, 'storeConvite'])
            ->name('voluntarios.convite.store');

        Route::post('/voluntarios/{voluntario}/reenviar-convite', [VoluntarioController::class, 'reenviarConvite'])
            ->name('voluntarios.convite.reenviar');

        Route::delete('/voluntarios/{voluntario}/convite', [VoluntarioController::class, 'cancelarConvite'])
            ->name('voluntarios.convite.cancelar');

        Route::resource('/voluntarios', VoluntarioController::class)
            ->parameters(['voluntarios' => 'voluntario'])
            ->except(['show']);

        Route::resource('/hospitais', HospitalController::class)->parameters(['hospitais' => 'hospital']);
        Route::resource('/patrocinadores', PatrocinadorController::class)->parameters(['patrocinadores' => 'patrocinador']);
    });

    Route::post('/eventos/{evento}/finalizar', [EventoFinalizacaoController::class, 'store'])->name('eventos.finalizar');
    Route::put('/eventos/{evento}/presencas', [EventoPresencaController::class, 'update'])->name('eventos.presencas.update');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
