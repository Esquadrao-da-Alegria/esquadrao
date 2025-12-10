<?php

use App\Http\Controllers\Web\HospitalController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\InvitationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PerfilController;

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';

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

// Route::post('/login', [AuthenticatedSessionController::class, 'store'])
//     ->middleware('guest');

//solicitar convite
Route::get('/send-invitation', [InvitationController::class, 'sendInvitation'])
    ->name('send-invitation');

Route::post('/send-invitation', [InvitationController::class, 'store'])
    ->name('store-invitation');

//logout
// Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
//     ->middleware('auth')
//     ->name('logout');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
    
            $user = auth()->user();
    

            $allPermissions = $user->getAllPermissions()->pluck('name')->toArray();
    

            $permissionsFormatted = [];
    
            foreach ($allPermissions as $permissionName) {

                $key = str_replace(' ', '_', $permissionName);
                $permissionsFormatted[$key] = true;
            }
    
            return Inertia::render('dashboard', [
                'permissions' => $permissionsFormatted,
                'role' => $user->roles->pluck('name')->first(),
            ]);
        })->name('dashboard');

    Route::get('/perfil', function() {
        $user = auth()->user();

        $allPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        $permissionsFormatted = [];

        foreach ($allPermissions as $permissionName) {
            $key = str_replace(' ', '_', $permissionName);
            $permissionsFormatted[$key] = true;
        }

        return Inertia::render('Perfil/Index', [
            'user' => $user,
            'permissions' => $permissionsFormatted,
            'role' => $user->roles->pluck('name')->first(),
        ]);
    })->name('perfil');


    

});
    


    //hospitais do projeto antigo
// Route::prefix('hospitais')->middleware('auth')->group(function () {
//     Route::get('/', [HospitalController::class, 'index'])
//         ->middleware('role:view_hospitais')
//         ->name('hospitais.index');

//     Route::get('/cadastrar', [HospitalController::class, 'create'])
//         ->middleware('role:create_hospitais')
//         ->name('hospitais.create');

//     Route::post('/', [HospitalController::class, 'store'])
//         ->middleware('role:create_hospitais')
//         ->name('hospitais.store');

//     Route::get('/{hospital}/editar', [HospitalController::class, 'edit'])
//         ->middleware('role:manage_hospitais')
//         ->name('hospitais.edit');

//     Route::put('/{hospital}', [HospitalController::class, 'update'])
//         ->middleware('role:manage_hospitais')
//         ->name('hospitais.update');

//     Route::delete('/{hospital}', [HospitalController::class, 'destroy'])
//         ->middleware('role:delete_hospitais')
//         ->name('hospitais.destroy');
// });

    //gerenciamento de usuario

Route::middleware(['role:diretor'])->group(function (){

    Route::get('/user-management', [UserManagementController::class, 'index'])

        ->name('user-management.index');

    Route::post('/user-management/{user}/update-role', [UserManagementController::class, 'updateRole'])

        ->name('user-management.update-role');

    Route::post('/user-management/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])

        ->name('user-management.toggle-active');
});

    



