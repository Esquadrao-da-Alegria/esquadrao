<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        $user = $request->user();
        
        if (!$user || !$user->role) {
            abort(403, 'Usuário não autenticado ou sem role definido');
        }

        $userRole = $user->role->nomeRole;
        
        // definir permissões por role
        $permissions = $this->getRolePermissions($userRole);
        
        if (!in_array($permission, $permissions)) {
            abort(403, 'Você não tem permissão para acessar esta funcionalidade');
        }

        return $next($request);
    }

    //permissoes baseadas no role
    private function getRolePermissions($role): array
    {
        switch ($role) {
            case 'admin':
            case 'diretor':
                return [
                    'manage_users',
                    'manage_voluntarios',
                    'manage_hospitais', 
                    'manage_visitas',
                    'delete_voluntarios',
                    'delete_hospitais',
                    'create_voluntarios',
                    'create_hospitais',
                    'create_visitas',
                    'view_voluntarios',
                    'view_hospitais',
                    'view_visitas'
                ];
                
            case 'coordenador':
                return [
                    'manage_voluntarios',
                    'manage_hospitais',
                    'manage_visitas', 
                    'create_voluntarios',
                    'create_hospitais',
                    'create_visitas',
                    'view_voluntarios',
                    'view_hospitais',
                    'view_visitas'
                ];
                
            case 'voluntario':
                return [
                    'create_visitas',
                    'view_visitas',
                    'view_hospitais',
                    'view_voluntarios'
                ];
                
            default:
                return [];
        }
    }
}
