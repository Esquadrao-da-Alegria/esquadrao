<?php

namespace App\Services\Visita\Relatorio;

use App\Enums\StatusParticipacao;
use App\Enums\VisitaStatus;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaRelatorio;
use App\Notifications\RelatorioVisitaNotification;
use App\Queries\Visita\Relatorio\Queries;
use App\Services\Visita\Service as VisitaService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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
            $retornoDatabase = $this->queries->index($visita->id);

            if (! $retornoDatabase['sucesso']) {
                $this->logarErro(
                    $this->payloadLogErro($visita->id),
                    'listar',
                    $retornoDatabase['erros'][0] ?? 'Erro desconhecido',
                );

                return $retornoDatabase;
            }

            return $retornoDatabase;
        } catch (\Throwable $th) {
            $this->logarErro(
                $this->payloadLogErro($visita->id),
                'listar',
                formatarMensagemErro($th),
            );

            return $this->erro(formatarMensagemErro($th));
        }
    }

    public function show(Visita $visita, VisitaRelatorio $relatorio): array
    {
        try {
            if ((int) $relatorio->visita_id !== (int) $visita->id) {
                return $this->erro('Relatório não pertence a esta visita.');
            }

            $retornoDatabase = $this->queries->show($relatorio->id);

            if (! $retornoDatabase['sucesso']) {
                $this->logarErro(
                    $this->payloadLogErro($visita->id, $relatorio->id, $relatorio->autor_id),
                    'exibir',
                    $retornoDatabase['erros'][0] ?? 'Erro desconhecido',
                );

                return $retornoDatabase;
            }

            return $retornoDatabase;
        } catch (\Throwable $th) {
            $this->logarErro(
                $this->payloadLogErro($visita->id, $relatorio->id, $relatorio->autor_id),
                'exibir',
                formatarMensagemErro($th),
            );

            return $this->erro(formatarMensagemErro($th));
        }
    }

    public function store(Visita $visita, array $dados): array
    {
        DB::beginTransaction();

        try {
            if ($visita->status === VisitaStatus::Cancelada) {
                DB::rollBack();

                return $this->erro('Não é possível criar relatório para visita cancelada.');
            }

            /** @var User $usuario */
            $usuario = Auth::user();

            if (! $this->usuarioParticipouDaVisita($usuario, $visita)) {
                DB::rollBack();

                return $this->erro('Apenas voluntários que participaram desta visita podem criar relatórios.');
            }

            $payload = $this->formatarDatabase($visita, $dados, 'store');

            $retornoDatabase = $this->queries->store($payload);

            if (! $retornoDatabase['sucesso']) {
                DB::rollBack();

                $this->logarErro(
                    $this->payloadLogErro($visita->id, null, Auth::id()),
                    'criar',
                    $retornoDatabase['erros'][0] ?? 'Erro desconhecido',
                );

                return $retornoDatabase;
            }

            if (in_array($visita->status, [VisitaStatus::Agendada, VisitaStatus::PendenteRelatorio], true)) {
                $visita->update(['status' => VisitaStatus::Realizada->value]);
            }

            DB::commit();

            $relatorioModel = $retornoDatabase['dados']['model'] ?? null;
            if ($relatorioModel instanceof VisitaRelatorio) {
                $this->notificarIntegrantesDaVisita($visita, $relatorioModel);
            }

            return $retornoDatabase;
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logarErro(
                $this->payloadLogErro($visita->id, null, Auth::id()),
                'criar',
                formatarMensagemErro($th),
            );

            return $this->erro(formatarMensagemErro($th));
        }
    }

    public function update(Visita $visita, VisitaRelatorio $relatorio, array $dados, User $user): array
    {
        DB::beginTransaction();

        try {
            if ((int) $relatorio->visita_id !== (int) $visita->id) {
                DB::rollBack();

                return $this->erro('Relatório não pertence a esta visita.');
            }

            if ($visita->status === VisitaStatus::Cancelada) {
                DB::rollBack();

                return $this->erro('Não é possível editar relatório de visita cancelada.');
            }

            if (! $this->podeEditarRelatorio($user, $visita, $relatorio)) {
                DB::rollBack();

                return $this->erro('Você não tem permissão para editar este relatório.');
            }

            $payload = $this->formatarDatabase($visita, $dados, 'update');

            $retornoDatabase = $this->queries->update($relatorio->id, $payload);

            if (! $retornoDatabase['sucesso']) {
                DB::rollBack();

                $this->logarErro(
                    $this->payloadLogErro($visita->id, $relatorio->id, $relatorio->autor_id),
                    'atualizar',
                    $retornoDatabase['erros'][0] ?? 'Erro desconhecido',
                );

                return $retornoDatabase;
            }

            DB::commit();

            return $retornoDatabase;
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logarErro(
                $this->payloadLogErro($visita->id, $relatorio->id, $relatorio->autor_id),
                'atualizar',
                formatarMensagemErro($th),
            );

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

    public function usuarioParticipouDaVisita(User $user, Visita $visita): bool
    {
        if ($visita->lider_id !== null && (int) $visita->lider_id === (int) $user->id) {
            return true;
        }

        return $visita->participantes()
            ->where('voluntario_id', $user->id)
            ->whereIn('status_participacao', [
                \App\Enums\StatusParticipacao::Confirmado->value,
                \App\Enums\StatusParticipacao::Pendente->value,
                \App\Enums\StatusParticipacao::Falta->value,
            ])
            ->exists();
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

        $relatorio->loadMissing([
            'autor:id,name',
            'alaUnidade:id,nome,hospital_id',
        ]);

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
            'ala_unidade_id'                 => $dados['ala_unidade_id'] ?? null,
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

    private function payloadLogErro(?int $visitaId = null, ?int $relatorioId = null, ?int $autorId = null): array
    {
        $payload = [];

        if ($visitaId !== null) {
            $payload['visita_id'] = $visitaId;
        }

        if ($relatorioId !== null) {
            $payload['relatorio_id'] = $relatorioId;
        }

        if ($autorId !== null) {
            $payload['autor_id'] = $autorId;
        }

        return $payload;
    }

    private function logarErro(array $dados, string $acao, string $mensagemErro): void
    {
        Log::error("Erro ao {$acao} relatório de visita!", [
            'sucesso' => false,
            'dados'   => $dados,
            'erros'   => [$mensagemErro],
        ]);
    }

    private function notificarIntegrantesDaVisita(Visita $visita, VisitaRelatorio $relatorio): void
    {
        try {
            $visita->load(['hospital', 'participantes.voluntario']);

            $integrantes = $visita->participantes
                ->filter(fn ($p) => $p->status_participacao !== StatusParticipacao::Cancelado)
                ->map(fn ($p) => $p->voluntario)
                ->filter(fn ($user) => $user !== null && ! empty($user->email))
                ->unique('id');

            if ($integrantes->isNotEmpty()) {
                Notification::send($integrantes, new RelatorioVisitaNotification($visita, $relatorio));
            }
        } catch (\Throwable $th) {
            $this->logarErro(
                $this->payloadLogErro($visita->id, $relatorio->id, Auth::id()),
                'notificar_integrantes',
                formatarMensagemErro($th),
            );
        }
    }
}
