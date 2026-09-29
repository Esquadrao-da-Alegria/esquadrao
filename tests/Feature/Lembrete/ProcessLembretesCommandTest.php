<?php

namespace Tests\Feature\Lembrete;

use App\Jobs\Lembrete\Processamento\Job as ProcessamentoJob;
use App\Models\Lembrete;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ProcessLembretesCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_despacha_job_para_lembretes_pendentes_vencidos(): void
    {
        Queue::fake();

        $vencido = Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 1,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => now()->subMinutes(5),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $this->artisan('lembretes:processar')->assertExitCode(0);

        Queue::assertPushed(ProcessamentoJob::class, function (ProcessamentoJob $job) use ($vencido) {
            return $this->lembreteIdDoJob($job) === $vencido->id;
        });
        Queue::assertPushed(ProcessamentoJob::class, 1);
    }

    public function test_nao_despacha_lembrete_pendente_ainda_nao_vencido(): void
    {
        Queue::fake();

        Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 1,
            'tipo' => Lembrete::TIPO_EVENTO_24H,
            'programado_para' => now()->addHour(),
            'status' => Lembrete::STATUS_PENDENTE,
        ]);

        $this->artisan('lembretes:processar')->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    public function test_nao_despacha_lembrete_ja_processado_ou_cancelado(): void
    {
        Queue::fake();

        Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 1,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => now()->subMinutes(5),
            'status' => Lembrete::STATUS_PROCESSADO,
            'processado_em' => now(),
        ]);

        Lembrete::query()->create([
            'atividade_tipo' => 'evento',
            'atividade_id' => 2,
            'tipo' => Lembrete::TIPO_EVENTO_1H,
            'programado_para' => now()->subMinutes(5),
            'status' => Lembrete::STATUS_CANCELADO,
            'cancelado_em' => now(),
        ]);

        $this->artisan('lembretes:processar')->assertExitCode(0);

        Queue::assertNothingPushed();
    }

    private function lembreteIdDoJob(ProcessamentoJob $job): int
    {
        $reflection = new \ReflectionProperty($job, 'lembreteId');
        $reflection->setAccessible(true);

        return $reflection->getValue($job);
    }
}
