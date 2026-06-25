<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MeuEventoController extends Controller
{
    public function index(Request $request)
    {
        $eventos = $request->user()
            ->eventosInscritos()
            ->with('responsavel:id,name')
            ->get()
            ->map(fn ($evento) => [
                'id' => $evento->id,
                'titulo' => $evento->titulo,
                'tipo' => $evento->tipo,
                'data_inicio' => $evento->data_inicio,
                'data_fim' => $evento->data_fim,
                'local' => $evento->local,
                'status' => $evento->status,
                'responsavel' => $evento->responsavel,
                'inscricao_status' => $evento->pivot->status,
                'presenca' => $evento->pivot->presenca,
                'presenca_registrada_em' => $evento->pivot->presenca_registrada_em,
            ])
            ->sortBy('data_inicio')
            ->values();

        return Inertia::render('Evento/MeusEventos', ['eventos' => $eventos]);
    }
}