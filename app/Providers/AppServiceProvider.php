<?php

namespace App\Providers;

use App\Models\User;
use App\Services\Dashboard\Permissao\Service as DashboardPermissaoService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (DashboardPermissaoService::PERMISSOES as $permissao) {
            Gate::define(
                $permissao,
                fn (User $user) => app(DashboardPermissaoService::class)->permite($user, $permissao)
            );
        }
    }
}
