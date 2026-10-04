<?php

namespace App\Services\Lembrete\Processamento;

// LIBS EXTERNAS
use Illuminate\Support\Collection;

// JOBS / ENUMS
use App\Enums\StatusParticipacao;
use App\Enums\VisitaStatus;
use App\Jobs\Lembrete\Entrega\Job as EntregaJob;

// MODELS
use App\Models\Evento;
use App\Models\Lembrete;
use App\Models\LembreteEntrega;
use App\Models\PushSubscription;
use App\Models\Visita;
use App\Models\VisitaRelatorio;

class Service
{
    public function processar(Lembrete $lembrete): void
    {
        if (! $lembrete->estaPendente()) {
            return;
        }

        $janelaMinutos = (int) config('webpush.lembretes.janela_validade_minutos', 60);

        if ($lembrete->programado_para->lt(now()->subMinutes($janelaMinutos))) {
            $this->cancelar($lembrete);

            return;
        }

        $destinatarios = match ($lembrete->atividade_tipo) {
            'evento' => $this->destinatariosEvento($lembrete),
            'visita' => $this->destinatariosVisita($lembrete),
            default => collect(),
        };

        foreach ($destinatarios as $subscription) {
            $entrega = LembreteEntrega::query()->firstOrCreate([
                'atividade_tipo' => $lembrete->atividade_tipo,
                'atividade_id' => $lembrete->atividade_id,
                'tipo' => $lembrete->tipo,
                'push_subscription_id' => $subscription->id,
            ], [
                'lembrete_id' => $lembrete->id,
                'user_id' => $subscription->user_id,
                'status' => LembreteEntrega::STATUS_PENDENTE,
            ]);

            if ($entrega->status !== LembreteEntrega::STATUS_SUCESSO) {
                EntregaJob::dispatch($entrega->id, $this->notificacao($lembrete));
            }
        }

        $lembrete->update([
            'status' => Lembrete::STATUS_PROCESSADO,
            'processado_em' => now(),
        ]);
    }

    public function destinatariosEvento(Lembrete $lembrete): Collection
    {
        $evento = Evento::query()->find($lembrete->atividade_id);

        if (! $evento || ! $evento->estaAgendado() || $evento->estaCancelado()) {
            return collect();
        }

        $tiposPermitidos = config('webpush.lembretes.eventos.tipos_permitidos', ['reuniao', 'oficina', 'evento']);
        if (! in_array($evento->tipo, $tiposPermitidos, true)) {
            return collect();
        }

        return PushSubscription::query()->ativas()
            ->whereIn('user_id', fn ($query) => $query
                ->select('user_id')
                ->from('evento_participantes')
                ->where('evento_id', $evento->id)
                ->where('status', 'inscrito'))
            ->get();
    }

    public function destinatariosVisita(Lembrete $lembrete): Collection
    {
        $visita = Visita::query()->with('participantes')->find($lembrete->atividade_id);

        if (! $visita || $visita->status === VisitaStatus::Cancelada || $visita->status?->value === 'cancelada') {
            return collect();
        }

        // MVP: envia para todos os participantes ativos que ainda não enviaram o relatório obrigatório
        $ids = $visita->participantes
            ->filter(fn ($participante) => $participante->status_participacao === StatusParticipacao::Confirmado || $participante->status_participacao?->value === 'confirmado')
            ->pluck('voluntario_id')
            ->reject(fn ($userId) => VisitaRelatorio::query()->where('visita_id', $visita->id)->where('autor_id', $userId)->exists());

        return PushSubscription::query()->ativas()->whereIn('user_id', $ids)->get();
    }

    private function notificacao(Lembrete $lembrete): array
    {
        if ($lembrete->atividade_tipo === 'visita') {
            return [
                'title' => 'Lembrete de Relatório de Visita',
                'body' => 'A visita recente foi finalizada. Registre seu relatório para atualizar o histórico da atividade.',
                'url' => route('visitas.relatorios.create', ['visita' => $lembrete->atividade_id]),
            ];
        }

        $intervaloTexto = $lembrete->tipo === Lembrete::TIPO_EVENTO_24H ? '24 horas' : '1 hora';

        return [
            'title' => 'Lembrete de Atividade',
            'body' => "Sua atividade começará em {$intervaloTexto}. Confira os detalhes e orientações.",
            'url' => route('eventos.show', ['evento' => $lembrete->atividade_id]),
        ];
    }

    private function cancelar(Lembrete $lembrete): void
    {
        $lembrete->update([
            'status' => Lembrete::STATUS_CANCELADO,
            'cancelado_em' => now(),
        ]);
    }
}
