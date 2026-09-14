<?php

namespace App\Console\Commands;

use App\Models\Evento;
use Illuminate\Console\Command;

class NotificarEvento extends Command
{
    protected $signature = 'eventos:notificar';

    protected $description = 'Notifica eventos agendados';

    public function handle(): int
    {
        $eventos = Evento::where('status', 'agendado')
            ->get();

        foreach ($eventos as $evento) {

            $this->info("Evento encontrado: {$evento->titulo}");

            foreach ($evento->participantesAtivos as $participante) {

                $this->info(
                    "Participante: {$participante->name}"
                );

            }
        }

        $this->info('Verificação executada.');

        return self::SUCCESS;
    }
}