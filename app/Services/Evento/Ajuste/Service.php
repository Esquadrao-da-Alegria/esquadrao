<?php

namespace App\Services\Evento\Ajuste;

use App\Enums\TipoAjusteEvento;
use App\Models\Evento;
use App\Models\EventoAjusteParticipacao;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Service
{
    public function store(Evento $evento, User $administrador, array $dados): EventoAjusteParticipacao
    {
        abort_unless($administrador->temCargo('administrador'), 403);

        if ($evento->estaCancelado()) throw ValidationException::withMessages(['evento' => 'Evento cancelado não pode receber ajustes.']);
        if ($evento->data_inicio->isFuture()) throw ValidationException::withMessages(['evento' => 'O ajuste fica disponível somente após o início do evento.']);

        $voluntario = User::query()->whereNotNull('voluntario_id')->findOrFail($dados['voluntario_id']);
        if ($administrador->is($voluntario)) throw ValidationException::withMessages(['voluntario_id' => 'O administrador não pode registrar um ajuste para si mesmo.']);

        return DB::transaction(function () use ($evento, $administrador, $voluntario, $dados) {
            $participacao = DB::table('evento_participantes')->where('evento_id', $evento->id)->where('user_id', $voluntario->id)->first();
            $anteriores = $participacao ? (array) $participacao : null;
            $tipo = TipoAjusteEvento::from($dados['tipo']);

            if ($tipo === TipoAjusteEvento::CorrecaoPresenca && (! $participacao || $participacao->status !== 'inscrito')) {
                throw ValidationException::withMessages(['voluntario_id' => 'A presença só pode ser corrigida para uma inscrição ativa.']);
            }

            $posteriores = $tipo === TipoAjusteEvento::CorrecaoInscricao
                ? ['status' => 'inscrito', 'inscrito_em' => $participacao->inscrito_em ?? now(), 'cancelado_em' => null]
                : ['presenca' => $dados['presenca'], 'presenca_registrada_em' => now(), 'presenca_registrada_por_id' => $administrador->id, 'observacao_presenca' => $dados['justificativa']];

            if ($participacao) {
                DB::table('evento_participantes')->where('id', $participacao->id)->update([...$posteriores, 'updated_at' => now()]);
            } else {
                DB::table('evento_participantes')->insert(['evento_id' => $evento->id, 'user_id' => $voluntario->id, ...$posteriores, 'created_at' => now(), 'updated_at' => now()]);
            }

            return EventoAjusteParticipacao::query()->create([
                'evento_id' => $evento->id, 'voluntario_id' => $voluntario->id, 'administrador_id' => $administrador->id,
                'tipo' => $tipo, 'justificativa' => $dados['justificativa'], 'dados_anteriores' => $anteriores, 'dados_posteriores' => $posteriores,
            ]);
        });
    }
}
