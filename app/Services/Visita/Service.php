<?php

namespace App\Services\Visita;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Enums\VisitaTipo;
use App\Models\User;
use App\Models\Hospital;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Queries\Visita\Queries;
use App\Services\Visita\Agenda\Liberacao\Service as LiberacaoAgendaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __construct(
        private Queries $queries,
        private LiberacaoAgendaService $liberacaoAgendaService,
    ) {}

    public function index(array $filtros): array
    {
        try {
            $retornoDatabase = $this->queries->index($filtros);

            if (! $retornoDatabase['sucesso']) {
                $this->logarErro(
                    $this->payloadLogErro(null, $filtros),
                    'listar',
                    $retornoDatabase['erros'][0] ?? 'Erro desconhecido',
                );

                session()->flash('mensagem_erro', 'Erro ao listar visitas!');
            }

            return $retornoDatabase;
        } catch (\Throwable $th) {
            $this->logarErro(
                $this->payloadLogErro(null, $filtros),
                'listar',
                formatarMensagemErro($th),
            );

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function podeEditarVisita(User $user, Visita $visita): bool
    {
        if ($visita->lider_id !== null && $user->id === $visita->lider_id) {
            return true;
        }

        resolverUsuario($user);
        $visita->loadMissing('hospital');

        foreach ($user->cargos as $cargo) {
            if (in_array($cargo->slug, ['administrador', 'diretor', 'coordenador_geral'], true)) {
                return true;
            }

            if ($cargo->slug === 'coordenador_local') {
                return $user->voluntario?->cidade_base_id !== null
                    && $visita->hospital?->cidade_id !== null
                    && (int) $user->voluntario->cidade_base_id === (int) $visita->hospital->cidade_id;
            }
        }

        return false;
    }

    public function store(array $dados): array
    {
        DB::beginTransaction();

        $contextoLog = [];

        try {
            $payload     = $this->formatarDatabase($dados, 'store');
            $contextoLog = $payload;

            if (Carbon::parse($payload['fim_em'])->lte(Carbon::parse($payload['inicio_em']))) {
                DB::rollBack();

                return $this->erroEnvelope('O horário de fim deve ser posterior ao início.');
            }

            $erroAgenda = $this->validarAgendaHospitalarSeNecessario($payload);

            if ($erroAgenda !== null) {
                DB::rollBack();

                return $this->erroEnvelope($erroAgenda);
            }

            $retornoDatabase = $this->queries->store($payload);

            if (! $retornoDatabase['sucesso']) {
                DB::rollBack();

                $this->logarErro(
                    $this->payloadLogErro(null, $contextoLog),
                    'criar',
                    $retornoDatabase['erros'][0] ?? 'Erro desconhecido',
                );

                return $retornoDatabase;
            }

            $visita = $retornoDatabase['dados']['model'];

            VisitaParticipante::query()->create([
                'visita_id'           => $visita->id,
                'voluntario_id'       => $payload['lider_id'],
                'tipo_participacao'   => TipoParticipacao::Palhaco->value,
                'papel_na_visita'     => PapelNaVisita::Participante->value,
                'status_participacao' => StatusParticipacao::Confirmado->value,
            ]);

            DB::commit();

            session()->flash('mensagem_sucesso', 'Visita cadastrada com sucesso!');

            return $retornoDatabase;
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logarErro(
                $this->payloadLogErro(null, $contextoLog),
                'criar',
                formatarMensagemErro($th),
            );

            return $this->erroEnvelope(formatarMensagemErro($th));
        }
    }

    public function update(Visita $visita, array $dados): array
    {
        DB::beginTransaction();

        $contextoLog = [];

        try {
            $payload     = $this->formatarDatabase($dados, 'update');

            if (! array_key_exists('hospital_id', $dados)) {
                $payload['hospital_id'] = $visita->hospital_id;
                $payload['ala_unidade_id'] = $visita->ala_unidade_id;
            }

            $contextoLog = $payload;

            if (Carbon::parse($payload['fim_em'])->lte(Carbon::parse($payload['inicio_em']))) {
                DB::rollBack();

                return $this->erroEnvelope('O horário de fim deve ser posterior ao início.');
            }

            $inicioAlterado = Carbon::parse($payload['inicio_em'])->ne(Carbon::parse($visita->inicio_em));

            if ($inicioAlterado) {
                $erroAgenda = $this->validarAgendaHospitalarSeNecessario($payload);

                if ($erroAgenda !== null) {
                    DB::rollBack();

                    return $this->erroEnvelope($erroAgenda);
                }
            }

            $retornoDatabase = $this->queries->update($visita->id, $payload);

            if (! $retornoDatabase['sucesso']) {
                DB::rollBack();

                $this->logarErro(
                    $this->payloadLogErro($visita->id, $contextoLog),
                    'atualizar',
                    $retornoDatabase['erros'][0] ?? 'Erro desconhecido',
                );

                return $retornoDatabase;
            }

            $this->garantirParticipacaoDoLider($visita->id, $payload['lider_id']);

            DB::commit();

            session()->flash('mensagem_sucesso', 'Dados gerais da visita atualizados com sucesso!');

            return $retornoDatabase;
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logarErro(
                $this->payloadLogErro($visita->id, $contextoLog),
                'atualizar',
                formatarMensagemErro($th),
            );

            return $this->erroEnvelope(formatarMensagemErro($th));
        }
    }

    private function validarAgendaHospitalarSeNecessario(array $payload): ?string
    {
        if (! $this->exigeValidacaoAgenda($payload)) {
            return null;
        }

        return $this->validarAgendaHospitalar((int) $payload['hospital_id'], $payload['inicio_em']);
    }

    private function exigeValidacaoAgenda(array $payload): bool
    {
        $tipo = $payload['tipo'] instanceof VisitaTipo ? $payload['tipo']->value : $payload['tipo'];

        return $tipo === VisitaTipo::Hospital->value && ! empty($payload['hospital_id']);
    }

    private function validarAgendaHospitalar(int $hospitalId, string $inicioEm): ?string
    {
        $hospital = Hospital::query()->find($hospitalId, ['id', 'cidade_id']);

        if (! $hospital?->cidade_id) {
            return 'Hospital inválido.';
        }

        $data = Carbon::parse($inicioEm);

        if (! $this->liberacaoAgendaService->mesEstaLiberado(
            (int) $hospital->cidade_id,
            (int) $data->year,
            (int) $data->month,
        )) {
            return 'A agenda desta cidade não está liberada para o mês selecionado.';
        }

        return null;
    }

    private function garantirParticipacaoDoLider(int $visitaId, int $liderId): void
    {
        $participacao = VisitaParticipante::query()
            ->where('visita_id', $visitaId)
            ->where('voluntario_id', $liderId)
            ->first();

        if ($participacao) {
            if (! in_array($participacao->status_participacao, [
                StatusParticipacao::Confirmado,
                StatusParticipacao::Pendente,
            ], true)) {
                $participacao->update([
                    'status_participacao' => StatusParticipacao::Confirmado->value,
                ]);
            }

            return;
        }

        VisitaParticipante::query()->create([
            'visita_id'           => $visitaId,
            'voluntario_id'       => $liderId,
            'tipo_participacao'   => TipoParticipacao::Palhaco->value,
            'papel_na_visita'     => PapelNaVisita::Participante->value,
            'status_participacao' => StatusParticipacao::Confirmado->value,
        ]);
    }

    private function formatarDatabase(array $dados, string $acao): array
    {
        $inicioEm = $this->montarDatetime($dados['data'], $dados['hora_inicio']);
        $fimEm    = $this->montarDatetime($dados['data'], $dados['hora_fim']);

        $tipo = $dados['tipo'] instanceof VisitaTipo ? $dados['tipo']->value : $dados['tipo'];
        $exigeHospital = in_array($tipo, [VisitaTipo::Hospital->value, VisitaTipo::Residencia->value, 'hospital', 'residencia'], true);

        $hospitalId = $exigeHospital ? ($dados['hospital_id'] ?? null) : ($dados['hospital_id'] ?? null);
        $alaId      = $hospitalId ? ($dados['ala_unidade_id'] ?? null) : null;

        $limite = null;
        if (array_key_exists('limite_participantes', $dados)) {
            $limite = ($dados['limite_participantes'] !== null && $dados['limite_participantes'] !== '')
                ? (int) $dados['limite_participantes']
                : null;
        } elseif ($acao === 'store' && $exigeHospital) {
            $limite = 5;
        }

        $payload = [
            'hospital_id'          => $hospitalId,
            'ala_unidade_id'       => $alaId,
            'lider_id'             => $dados['lider_id'],
            'inicio_em'            => $inicioEm,
            'fim_em'               => $fimEm,
            'tipo'                 => $tipo,
            'limite_participantes' => $limite,
            'observacoes'          => $dados['observacoes'] ?? null,
        ];

        if ($acao === 'store') {
            $payload['criado_por_id'] = Auth::id();
            $payload['status']        = VisitaStatus::Agendada->value;
            $payload['origem']        = VisitaOrigem::Sistema->value;
        }

        if ($acao === 'update') {
            $payload['status'] = $dados['status'];
        }

        return $payload;
    }

    private function montarDatetime(string $data, string $hora): string
    {
        return Carbon::createFromFormat('Y-m-d H:i', "{$data} {$hora}")->format('Y-m-d H:i:s');
    }

    private function erroEnvelope(string $mensagem): array
    {
        return ['sucesso' => false, 'dados' => [], 'erros' => [$mensagem]];
    }

    private function payloadLogErro(?int $visitaId = null, array $contexto = []): array
    {
        $payload = [];

        if ($visitaId !== null) {
            $payload['visita_id'] = $visitaId;
        }

        $camposPermitidos = [
            'lider_id',
            'hospital_id',
            'ala_unidade_id',
            'criado_por_id',
            'status',
            'tipo',
            'limite_participantes',
            'origem',
            'inicio_em',
            'fim_em',
            'mes',
            'id',
        ];

        foreach ($camposPermitidos as $campo) {
            if (array_key_exists($campo, $contexto)) {
                $payload[$campo] = $contexto[$campo];
            }
        }

        return $payload;
    }

    private function logarErro(array $dados, string $acao, string $mensagemErro): void
    {
        Log::error("Erro ao {$acao} visita!", [
            'sucesso' => false,
            'dados'   => $dados,
            'erros'   => ["Erro ao {$acao} visita: {$mensagemErro}"],
        ]);
    }
}
