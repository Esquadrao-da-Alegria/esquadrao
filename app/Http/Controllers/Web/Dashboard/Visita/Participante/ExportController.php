<?php

namespace App\Http\Controllers\Web\Dashboard\Visita\Participante;

use App\Http\Controllers\Controller as BaseController;
use App\Http\Requests\Web\Dashboard\Visita\Participante\IndexRequest;
use App\Services\Dashboard\Visita\Participante\Service;
use Carbon\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\Browsershot\Browsershot;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends BaseController
{
    public function __construct(private Service $service) {}

    public function __invoke(IndexRequest $request, string $formato): Response
    {
        abort_unless(in_array($formato, ['pdf', 'csv', 'xlsx'], true), 422);

        $participantes = $this->service->exportar($request->user(), $request->validated());
        $filtros = $request->validated();
        $nomeArquivo = $this->nomeArquivo($filtros, $formato);

        return match ($formato) {
            'pdf'  => $this->gerarPdf($participantes, $filtros, $nomeArquivo),
            'csv'  => $this->gerarPlanilha($participantes, $nomeArquivo . '.csv'),
            'xlsx' => $this->gerarPlanilha($participantes, $nomeArquivo . '.xlsx'),
        };
    }

    private function gerarPdf($participantes, array $filtros, string $nomeArquivo): Response
    {
        return Pdf::view('pdf.dashboard.participante', [
                'participantes' => $participantes,
                'filtros'       => $filtros,
                'geradoEm'      => now()->format('d/m/Y H:i'),
            ])
            ->format('a4')
            ->landscape()
            ->withBrowsershot(function (Browsershot $browsershot): void {
                $browsershot->setChromePath((string) env('CHROME_PATH', '/usr/bin/google-chrome-stable'))->noSandbox();
            })
            ->name($nomeArquivo . '.pdf')
            ->download()
            ->toResponse(request());
    }

    private function gerarPlanilha($participantes, string $nomeArquivo): Response
    {
        $writer = SimpleExcelWriter::streamDownload($nomeArquivo);
        $writer->addHeader([
            'Voluntário',
            'Cidade',
            'Cargos',
            'Tipo de atuação',
            'Visitas válidas',
            'Meta mensal',
            'Saldo atual',
            'Situação',
            'Reuniões (%)',
            'Oficinas (%)',
            'Rel. pendentes',
            'Rel. fora do prazo',
            'Última atividade',
            'Dias sem atividade',
        ]);

        $writer->addRows($participantes->map(fn ($p) => [
            $p['nome'],
            $p['cidade'],
            implode(', ', $p['cargos']->toArray()),
            $p['tipo_atuacao'],
            $p['visitas_validas'],
            $p['meta_mensal'] ?? '',
            $p['saldo_atual'] ?? '',
            $p['situacao'],
            $p['reunioes']['percentual'] ?? '',
            $p['oficinas']['percentual'] ?? '',
            $p['relatorios_pendentes'],
            $p['relatorios_fora_prazo'],
            $p['ultima_atividade'] ? Carbon::parse($p['ultima_atividade'])->format('d/m/Y') : '',
            $p['dias_sem_atividade'] ?? '',
        ])->toArray());

        return $writer->toBrowser();
    }

    private function nomeArquivo(array $filtros, string $formato): string
    {
        $tipo = $filtros['periodo_tipo'] ?? 'ano';
        $ano = $filtros['ano'] ?? now()->year;

        $sufixo = match ($tipo) {
            'mes'      => $ano . '-' . str_pad($filtros['mes'] ?? 1, 2, '0', STR_PAD_LEFT),
            'semestre' => $ano . '-s' . ($filtros['semestre'] ?? 1),
            default    => $ano,
        };

        return "voluntarios-{$sufixo}";
    }
}
