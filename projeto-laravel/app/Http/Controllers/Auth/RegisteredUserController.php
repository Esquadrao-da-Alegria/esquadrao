<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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
use Spatie\Permission\Models\Role;

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
            'email' => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'invitation_token' => 'required|string|exists:invitations,invitation_token',
            'profile_visibility' => 'required|in:public,private',
        ]);

        // Verificar se o convite é válido e não utilizado
        $invitation = Invitation::where('invitation_token', $request->invitation_token)
            ->whereNull('registered_at')
            ->first();

        if (!$invitation) {
            return back()
                ->withErrors(['invitation_token' => 'Convite inválido ou já utilizado.'])
                ->withInput();
        }

        // Verificar correspondência do email
        if ($invitation->email !== $request->email) {
            return back()
                ->withErrors(['email' => 'O email deve corresponder ao convite recebido.'])
                ->withInput();
        }

        // Verificar se é o primeiro usuário
        $isFirstUser = User::count() === 0;

        // Criar ou obter roles
        $diretorRole = Role::findOrCreate('diretor');
        $coordenadorRole = Role::findOrCreate('coordenador');
        $voluntarioRole = Role::findOrCreate('voluntario');

        // Criar o usuário
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'active' => true,
            'profile_visibility' => ProfileVisibility::from($request->profile_visibility),
        ]);

        // Atribuir role via Spatie
        if ($isFirstUser) {
            $user->assignRole($diretorRole);
        } else {
            $user->assignRole($voluntarioRole);
        }

        // Marcar o convite como utilizado
        $invitation->markAsRegistered();

        event(new Registered($user));

        Auth::login($user);

        // Mensagem especial para o primeiro usuário
        if ($isFirstUser) {
            return redirect('/')
                ->with('success', 'Parabéns! Você é o primeiro usuário e foi automaticamente definido como Diretor.');
        }

        return redirect('/')
            ->with('success', 'Conta criada com sucesso! Bem-vindo ao Esquadrão da Alegria!');
    }
}
