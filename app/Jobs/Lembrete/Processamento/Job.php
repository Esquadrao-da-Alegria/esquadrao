<?php

namespace App\Jobs\Lembrete\Processamento;

use App\Models\Lembrete;
use App\Services\Lembrete\Processamento\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $lembreteId)
    {
    }

    public function handle(Service $service): void
    {
        $lembrete = Lembrete::query()->find($this->lembreteId);

        if ($lembrete) {
            $service->processar($lembrete);
        }
    }
}
