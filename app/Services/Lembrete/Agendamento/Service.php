<?php

namespace App\Services\Lembrete\Agendamento;

use App\Enums\VisitaStatus;
use App\Models\Evento;
use App\Models\Lembrete;
use App\Models\Visita;

class Service
{
    public function visita(Visita $visita): void
    {
        $this->cancelar('visita', $visita->id);

        if ($visita->status === VisitaStatus::Cancelada) {
            return;
        }

        Lembrete::query()->create([
            'atividade_tipo' => 'visita',
            'atividade_id' => $visita->id,
            'tipo' => Lembrete::TIPO_RELATORIO_VISITA,
            'programado_para' => $visita->fim_em,
        ]);
    }

    public function evento(Evento $evento): void
    {
        $this->cancelar('evento', $evento->id);

        if (! $evento->estaAgendado() || ! in_array($evento->tipo, config('webpush.lembretes.eventos.tipos_permitidos'), true)) {
            return;
        }

        $this->criarEvento($evento, Lembrete::TIPO_EVENTO_24H, config('webpush.lembretes.eventos.intervalo_longo_horas'));
        $this->criarEvento($evento, Lembrete::TIPO_EVENTO_1H, config('webpush.lembretes.eventos.intervalo_curto_horas'));
    }

    public function cancelar(string $atividadeTipo, int $atividadeId): void
    {
        Lembrete::query()
            ->pendentes()
            ->where('atividade_tipo', $atividadeTipo)
            ->where('atividade_id', $atividadeId)
            ->update([
                'status' => Lembrete::STATUS_CANCELADO,
                'cancelado_em' => now(),
            ]);
    }

    private function criarEvento(Evento $evento, string $tipo, int $horas): void
    {
        Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => $evento->id,
            'tipo' => $tipo,
            'programado_para' => $evento->data_inicio->copy()->subHours($horas),
        ]);
    }
}
