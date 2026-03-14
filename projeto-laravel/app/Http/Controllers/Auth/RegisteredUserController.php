<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Invitation;
use App\Enums\ProfileVisibility;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invitation_token' => 'required|string|exists:invitations,invitation_token',
            'profile_visibility' => 'required|in:public,private',
        ]);

        // Verificar se o convite é válido e não foi usado
        $invitation = Invitation::where('invitation_token', $request->invitation_token)
            ->whereNull('registered_at')
            ->first();

        if (!$invitation) {
            return redirect()->back()
                ->withErrors(['invitation_token' => 'Convite inválido ou já utilizado.'])
                ->withInput();
        }

        // Verificar se o email do convite corresponde ao email do registro
        if ($invitation->email !== $request->email) {
            return redirect()->back()
                ->withErrors(['email' => 'O email deve corresponder ao convite recebido.'])
                ->withInput();
        }

        // Verificar se é o primeiro usuário do sistema
        $isFirstUser = User::count() === 0;
        
        if ($isFirstUser) {
            // Primeiro usuário recebe role de diretor
            $role = Role::where('nomeRole', 'diretor')->first();
            
            if (!$role) {
                // Se não existir role de diretor, criar
                $role = Role::create(['nomeRole' => 'diretor']);
            }
        } else {
            // Usuários subsequentes recebem role de voluntário
            $role = Role::where('nomeRole', 'voluntario')->first();
            
            if (!$role) {
                // Se não existir role de voluntário, criar
                $role = Role::create(['nomeRole' => 'voluntario']);
            }
        }
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $role->id,
            'active' => true,
            'profile_visibility' => ProfileVisibility::from($request->profile_visibility),
        ]);

        // Marcar o convite como usado
        $invitation->markAsRegistered();

        event(new Registered($user));

        Auth::login($user);

        // Mensagem especial para o primeiro usuário
        if ($isFirstUser) {
            return redirect()->intended(route('dashboard', absolute: false))
                ->with('success', 'Parabéns! Você é o primeiro usuário do sistema e foi automaticamente atribuído como Diretor. Bem-vindo ao Esquadrão do Sorriso!');
        }

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Conta criada com sucesso! Bem-vindo ao Esquadrão do Sorriso!');
    }
}
