<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAdministrador
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = usuarioAutenticado();

        if (! $user) {
            abort(403);
        }

        $ehAdministrador = $user->cargos->contains(fn ($cargo) => $cargo->slug === 'administrador');

        if (! $ehAdministrador) {
            abort(403);
        }

        return $next($request);
    }
}
