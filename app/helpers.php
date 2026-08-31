<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('relacionamentosUsuario')) {
    /**
     * @return list<string>
     */
    function relacionamentosUsuario(): array
    {
        return ['cargos', 'voluntario', 'voluntario.cidadeBase'];
    }
}

if (!function_exists('resolverUsuario')) {
    function resolverUsuario(User $user): User
    {
        $user->loadMissing(relacionamentosUsuario());

        return $user;
    }
}

if (!function_exists('usuarioAutenticado')) {
    function usuarioAutenticado(?string $guard = null): ?User
    {
        $user = Auth::guard($guard)->user();

        if (! $user instanceof User) {
            return null;
        }

        return resolverUsuario($user);
    }
}

if (!function_exists('formatarMensagemErro')) {
    function formatarMensagemErro(\Throwable $th): string
    {
        try {

            return $th->getMessage() . ' | ' . $th->getFile() . ' | ' . $th->getLine();
        } catch (\Throwable $th) {
            return 'Erro ao formatar mensagem de erro';
        }
    }
}

if (!function_exists('mensagemFlashSalvar')) {
    function mensagemFlashSalvar(bool $sucesso): void
    {
        if (!$sucesso) {

            session()->flash('mensagem_erro', 'Erro ao salvar dados!');

            return;
        }

        session()->flash('mensagem_sucesso', 'Dados salvos com sucesso!');
    }
}
