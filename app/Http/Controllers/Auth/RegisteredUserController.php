<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): RedirectResponse
    {
        return to_route('login')->with('status', 'Novos cadastros são realizados somente por convite de um administrador.');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        abort(403, 'Novos cadastros são realizados somente por convite de um administrador.');
    }
}
