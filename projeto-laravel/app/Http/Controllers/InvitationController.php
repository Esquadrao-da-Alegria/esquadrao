<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Http\Requests\StoreInvitationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    /**
     * Show the request invitation page.
     */
    public function requestInvitation()
    {
        return Inertia::render('auth/request-invitation');
    }

    /**
     * Store a new invitation request.
     */
    public function store(StoreInvitationRequest $request)
    {
        $invitation = new Invitation($request->validated());
        $invitation->generateInvitationToken();
        $invitation->save();

        // Enviar email com o convite
        $this->sendInvitationEmail($invitation);

        return redirect()->route('request-invitation')
            ->with('success', 'Convite solicitado com sucesso! Você receberá um email com o link para se registrar.');
    }

    /**
     * Show the registration page with invitation token.
     */
    public function showRegistration(Request $request, string $token = null)
    {
        // Se não há token, mostra página de registro normal
        if (!$token) {
            return Inertia::render('auth/register');
        }

        // Se há token, valida o convite
        $invitation = Invitation::where('invitation_token', $token)
            ->whereNull('registered_at')
            ->first();

        if (!$invitation) {
            return redirect()->route('login')
                ->with('error', 'Convite inválido ou já utilizado.');
        }

        return Inertia::render('auth/register', [
            'invitation' => $invitation,
            'token' => $token
        ]);
    }

    /**
     * Send invitation email.
     */
    private function sendInvitationEmail(Invitation $invitation)
    {
        $registrationUrl = route('register', ['token' => $invitation->invitation_token]);
        
        Mail::send('emails.invitation', [
            'invitation' => $invitation,
            'registrationUrl' => $registrationUrl
        ], function ($message) use ($invitation) {
            $message->to($invitation->email)
                ->subject('Convite para se registrar - Esquadrão da Alegria');
        });
    }
}
