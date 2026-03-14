<?php

use App\Http\Controllers\Web\HospitalController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

//publicas
Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

// hospitais
Route::get('/onde-atuamos', function () {
    return Inertia::render('OndeAtuamos/Index');
})->name('onde_atuamos.index');

// conheça
Route::get('/conheça', function () {
    return Inertia::render('Conheca/Index');
})->name('conheca.index');

// doações
Route::get('/doacoes', function () {
    return Inertia::render('Doacao/Index');
})->name('doacoes.index');

// fale Conosco
Route::get('/fale-conosco', function () {
    return Inertia::render('FaleConosco/Index');
})->name('fale_conosco.index');



//registro com convite
Route::get('/register/{token}', [InvitationController::class, 'showRegistration'])
    ->middleware('guest')
    ->name('register.with-token');

Route::get('/register', [InvitationController::class, 'showRegistration'])
    ->middleware('guest')
    ->name('register');

Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest');

//login
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

//solicitar convite
Route::get('/request-invitation', [InvitationController::class, 'requestInvitation'])
    ->middleware('guest')
    ->name('request-invitation');

Route::post('/request-invitation', [InvitationController::class, 'store'])
    ->middleware('guest')
    ->name('store-invitation');

//logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

//autenticadas
Route::middleware(['auth', 'verified'])->group(function () {

    //dashboard
    Route::get('dashboard', function () {
        $user = auth()->user();
        $permissions = [];

        if ($user && $user->role) {
            $userRole = $user->role->nomeRole;

            switch ($userRole) {
                case 'admin':
                case 'diretor':
                    $permissions = [
                        'manage_users' => true,
                        'manage_voluntarios' => true,
                        'manage_hospitais' => true,
                        'manage_visitas' => true,
                        'delete_voluntarios' => true,
                        'delete_hospitais' => true,
                        'create_voluntarios' => true,
                        'create_hospitais' => true,
                        'create_visitas' => true,
                        'view_voluntarios' => true,
                        'view_hospitais' => true,
                        'view_visitas' => true
                    ];
                    break;

                case 'coordenador':
                    $permissions = [
                        'manage_voluntarios' => true,
                        'manage_hospitais' => true,
                        'manage_visitas' => true,
                        'create_voluntarios' => true,
                        'create_hospitais' => true,
                        'create_visitas' => true,
                        'view_voluntarios' => true,
                        'view_hospitais' => true,
                        'view_visitas' => true,
                        'delete_voluntarios' => false,
                        'delete_hospitais' => false,
                        'manage_users' => false
                    ];
                    break;

                case 'voluntario':
                    $permissions = [
                        'create_visitas' => true,
                        'view_visitas' => true,
                        'manage_users' => false,
                        'manage_voluntarios' => false,
                        'manage_hospitais' => false,
                        'manage_visitas' => false,
                        'delete_voluntarios' => false,
                        'delete_hospitais' => false,
                        'create_voluntarios' => false,
                        'create_hospitais' => false,
                        'view_voluntarios' => false,
                        'view_hospitais' => false
                    ];
                    break;
            }
        }

        return Inertia::render('dashboard', [
            'permissions' => $permissions,
            'userRole' => $user->role->nomeRole ?? 'sem_role'
        ]);
    })->name('dashboard');

    //hospitais do projeto antigo
    Route::prefix('hospitais')->middleware('auth')->group(function () {
        Route::get('/', [HospitalController::class, 'index'])
            ->middleware('role:view_hospitais')
            ->name('hospitais.index');

        Route::get('/cadastrar', [HospitalController::class, 'create'])
            ->middleware('role:create_hospitais')
            ->name('hospitais.create');

        Route::post('/', [HospitalController::class, 'store'])
            ->middleware('role:create_hospitais')
            ->name('hospitais.store');

        Route::get('/{hospital}/editar', [HospitalController::class, 'edit'])
            ->middleware('role:manage_hospitais')
            ->name('hospitais.edit');

        Route::put('/{hospital}', [HospitalController::class, 'update'])
            ->middleware('role:manage_hospitais')
            ->name('hospitais.update');

        Route::delete('/{hospital}', [HospitalController::class, 'destroy'])
            ->middleware('role:delete_hospitais')
            ->name('hospitais.destroy');
    });

    //gerenciamento de usuario
    Route::get('/user-management', [UserManagementController::class, 'index'])
        ->middleware('role:manage_users')
        ->name('user-management.index');

    Route::post('/user-management/{user}/update-role', [UserManagementController::class, 'updateRole'])
        ->middleware('role:manage_users')
        ->name('user-management.update-role');

    Route::post('/user-management/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])
        ->middleware('role:manage_users')
        ->name('user-management.toggle-active');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
