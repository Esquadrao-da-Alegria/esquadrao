<?php

namespace App\Services\Visita\Relatorio;

use App\Enums\VisitaStatus;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaRelatorio;
use App\Queries\Visita\Relatorio\Queries;
use App\Services\Visita\Service as VisitaService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Facades\Pdf;
use Symfony\Component\HttpFoundation\Response;

class Service
{
    public function __construct(
        private Queries $queries,
        private VisitaService $visitaService,
    ) {}

    public function index(Visita $visita): array
    {
        try {
            return $this->queries->index($visita->id);
        } catch (\Throwable $th) {
            $this->logarErro(['visita_id' => $visita->id], 'listar', formatarMensagemErro($th));

            return $this->erro(formatarMensagemErro($th));
        }
    }

    public function show(Visita $visita, VisitaRelatorio $relatorio): array
    {
        try {
            if ((int) $relatorio->visita_id !== (int) $visita->id) {
                return $this->erro('Relatório não pertence a esta visita.');
            }

            $retorno = $this->queries->show($relatorio->id);

            if (! $retorno['sucesso']) {
                return $retorno;
            }

            return $retorno;
        } catch (\Throwable $th) {
            $this->logarErro([
                'visita_id'    => $visita->id,
                'relatorio_id' => $relatorio->id,
            ], 'exibir', formatarMensagemErro($th));

            return $this->erro(formatarMensagemErro($th));
        }
    }

    public function store(Visita $visita, array $dados): array
    {
        try {
            if ($visita->status === VisitaStatus::Cancelada) {
                return $this->erro('Não é possível criar relatório para visita cancelada.');
            }

            $payload = $this->formatarDatabase($visita, $dados, 'store');

            return $this->queries->store($payload);
        } catch (\Throwable $th) {
            $this->logarErro(['visita_id' => $visita->id, ...$dados], 'criar', formatarMensagemErro($th));

            return $this->erro(formatarMensagemErro($th));
        }
    }

    public function update(Visita $visita, VisitaRelatorio $relatorio, array $dados, User $user): array
    {
        try {
            if ((int) $relatorio->visita_id !== (int) $visita->id) {
                return $this->erro('Relatório não pertence a esta visita.');
            }

            if ($visita->status === VisitaStatus::Cancelada) {
                return $this->erro('Não é possível editar relatório de visita cancelada.');
            }

            if (! $this->podeEditarRelatorio($user, $visita, $relatorio)) {
                return $this->erro('Você não tem permissão para editar este relatório.');
            }

            $payload = $this->formatarDatabase($visita, $dados, 'update');

            return $this->queries->update($relatorio->id, $payload);
        } catch (\Throwable $th) {
            $this->logarErro([
                'visita_id'    => $visita->id,
                'relatorio_id' => $relatorio->id,
                ...$dados,
            ], 'atualizar', formatarMensagemErro($th));

            return $this->erro(formatarMensagemErro($th));
        }
    }

    public function podeEditarRelatorio(User $user, Visita $visita, VisitaRelatorio $relatorio): bool
    {
        if ($visita->status === VisitaStatus::Cancelada) {
            return false;
        }

        if ((int) $relatorio->autor_id === (int) $user->id) {
            return true;
        }

        return $this->visitaService->podeEditarVisita($user, $visita);
    }

    public function calcularForaDoPrazo(Visita $visita, CarbonInterface $enviadoEm): bool
    {
        return $enviadoEm->gt($visita->fim_em->copy()->addHours(48));
    }

    public function pdf(Visita $visita, VisitaRelatorio $relatorio): Response
    {
        if ((int) $relatorio->visita_id !== (int) $visita->id) {
            abort(404);
        }

        $visita->loadMissing([
            'hospital:id,nome,cidade_id',
            'alaUnidade:id,nome,hospital_id',
            'lider:id,name',
            'participantes.voluntario:id,name',
        ]);

        $relatorio->loadMissing('autor:id,name');

        return Pdf::view('pdf.visita.relatorio', [
            'visita'    => $visita,
            'relatorio' => $relatorio,
        ])
            ->format('a4')
            ->withBrowsershot(function (Browsershot $browsershot): void {
                $browsershot
                    ->setChromePath('/usr/bin/google-chrome-stable')
                    ->noSandbox();
            })
            ->name('relatorio-visita-'.$relatorio->id.'.pdf')
            ->download()
            ->toResponse(request());
    }

    private function formatarDatabase(Visita $visita, array $dados, string $acao): array
    {
        $payload = [
            'tipo_relatorio'                 => $dados['tipo_relatorio'],
            'resumo'                         => $dados['resumo'],
            'feedback'                       => $dados['feedback'] ?? null,
            'quartos_visitados'              => $dados['quartos_visitados'] ?? null,
            'pessoas_impactadas'             => $dados['pessoas_impactadas'] ?? null,
            'observacao_visitantes_externos' => $dados['observacao_visitantes_externos'] ?? null,
            'observacoes_gerais'             => $dados['observacoes_gerais'] ?? null,
        ];

        if ($acao === 'store') {
            $enviadoEm = now();

            $payload['visita_id']     = $visita->id;
            $payload['autor_id']      = Auth::id();
            $payload['enviado_em']    = $enviadoEm;
            $payload['fora_do_prazo'] = $this->calcularForaDoPrazo($visita, $enviadoEm);
        }

        return $payload;
    }

    private function erro(string $mensagem): array
    {
        return ['sucesso' => false, 'dados' => [], 'erros' => [$mensagem]];
    }

    private function logarErro(array $dados, string $acao, string $mensagemErro): void
    {
        Log::error("Erro ao {$acao} relatório de visita!", [
            'sucesso' => false,
            'dados'   => $dados,
            'erros'   => [$mensagemErro],
        ]);
    }
}
