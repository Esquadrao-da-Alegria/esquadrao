<?php

namespace App\Providers;

// MODELS
use App\Models\User;
// SERVICES
use App\Services\Dashboard\Permissao\Service as DashboardPermissaoService;
// EVENTS
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Foundation\Http\Events\RequestHandled;
// FACADES
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
// PROVIDERS
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
        Gate::define('viewPulse', function (User $user): bool {
            return $user->cargos->contains('slug', 'administrador');
        });

        Event::listen(DiagnosingHealth::class, function (): void {
            DB::select('SELECT 1');
        });

        Event::listen(RequestHandled::class, function (RequestHandled $event): void {
            $status = $event->response->getStatusCode();

            if ($status < 500 || $status > 599) {
                return;
            }

            $metodo = $event->request->method();
            $rota = $event->request->route()?->uri();
            $contexto = [
                'status' => $status,
                'metodo' => $metodo,
                'rota' => $rota,
            ];

            Log::error('Resposta HTTP 5xx', $contexto);
        });

        foreach (DashboardPermissaoService::PERMISSOES as $permissao) {
            Gate::define(
                $permissao,
                fn (User $user) => app(DashboardPermissaoService::class)->permite($user, $permissao)
            );
        }
    }
}
