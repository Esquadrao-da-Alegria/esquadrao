<?php

namespace App\Console\Commands;

use App\Models\Evento;
use Illuminate\Console\Command;

class FinalizarEventosPassados extends Command
{
    protected $signature = 'eventos:finalizar-passados';
    protected $description = 'Finaliza automaticamente eventos agendados cujo prazo já encerrou';

    public function handle(): int
    {
        $count = Evento::where('status', 'agendado')
            ->where(function ($query) {
                $query->whereNotNull('data_fim')
                    ->where('data_fim', '<', now())
                    ->orWhere(function ($q) {
                        $q->whereNull('data_fim')->where('data_inicio', '<', now());
                    });
            })
            ->update(['status' => 'finalizado', 'finalizado_em' => now()]);

        $this->info("{$count} evento(s) finalizado(s) automaticamente.");

        return self::SUCCESS;
    }
}