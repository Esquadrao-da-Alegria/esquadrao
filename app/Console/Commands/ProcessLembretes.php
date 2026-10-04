<?php

namespace App\Console\Commands;

use App\Jobs\Lembrete\Processamento\Job;
use App\Models\Lembrete;
use Illuminate\Console\Command;

class ProcessLembretes extends Command
{
    protected $signature = 'lembretes:processar';

    protected $description = 'Envia para a fila os lembretes de atividades vencidos';

    public function handle(): int
    {
        Lembrete::query()->vencidos()->pluck('id')->each(
            fn (int $lembreteId) => Job::dispatch($lembreteId)
        );

        return self::SUCCESS;
    }
}
