<?php

namespace App\Services\Visita;

use App\Enums\PapelNaVisita;
use App\Enums\StatusParticipacao;
use App\Enums\TipoParticipacao;
use App\Enums\VisitaOrigem;
use App\Enums\VisitaStatus;
use App\Models\User;
use App\Models\Visita;
use App\Models\VisitaParticipante;
use App\Queries\Visita\Queries;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(array $filtros): array
    {
        try {
            $retorno = $this->queries->index($filtros);

            if (!$retorno['sucesso']) {
                session()->flash('mensagem_erro', 'Erro ao listar visitas!');
            }

            return $retorno;
        } catch (\Throwable $th) {
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

        $user->loadMissing(['cargos', 'voluntario']);
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
        try {
            $payload = $this->formatarDatabase($dados, 'store');

            if (Carbon::parse($payload['fim_em'])->lte(Carbon::parse($payload['inicio_em']))) {
                return $this->erroEnvelope('O horário de fim deve ser posterior ao início.');
            }

            DB::beginTransaction();

            try {
                $retorno = $this->queries->store($payload);

                if (! $retorno['sucesso']) {
                    DB::rollBack();

                    return $retorno;
                }

                $visita = $retorno['dados']['model'];

                VisitaParticipante::query()->create([
                    'visita_id'           => $visita->id,
                    'voluntario_id'       => $payload['lider_id'],
                    'tipo_participacao'   => TipoParticipacao::Palhaco->value,
                    'papel_na_visita'     => PapelNaVisita::Participante->value,
                    'status_participacao' => StatusParticipacao::Confirmado->value,
                ]);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollBack();

                throw $th;
            }

            if ($retorno['sucesso']) {
                session()->flash('mensagem_sucesso', 'Visita cadastrada com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {
            $this->logarErro($dados, 'criar', formatarMensagemErro($th));

            return $this->erroEnvelope(formatarMensagemErro($th));
        }
    }

    public function update(Visita $visita, array $dados): array
    {
        try {
            $payload = $this->formatarDatabase($dados, 'update');

            if (Carbon::parse($payload['fim_em'])->lte(Carbon::parse($payload['inicio_em']))) {
                return $this->erroEnvelope('O horário de fim deve ser posterior ao início.');
            }

            $retorno = $this->queries->update($visita->id, $payload);

            if ($retorno['sucesso']) {
                session()->flash('mensagem_sucesso', 'Visita atualizada com sucesso!');
            }

            return $retorno;
        } catch (\Throwable $th) {
            $this->logarErro(['visita_id' => $visita->id, ...$dados], 'atualizar', formatarMensagemErro($th));

            return $this->erroEnvelope(formatarMensagemErro($th));
        }
    }

    private function formatarDatabase(array $dados, string $acao): array
    {
        $inicioEm = $this->montarDatetime($dados['data'], $dados['hora_inicio']);
        $fimEm    = $this->montarDatetime($dados['data'], $dados['hora_fim']);

        $payload = [
            'lider_id'    => $dados['lider_id'],
            'inicio_em'   => $inicioEm,
            'fim_em'      => $fimEm,
            'tipo'        => $dados['tipo'],
            'observacoes' => $dados['observacoes'] ?? null,
        ];

        if ($acao === 'store') {
            $payload['hospital_id']    = $dados['hospital_id'];
            $payload['ala_unidade_id'] = $dados['ala_unidade_id'] ?? null;
            $payload['criado_por_id']  = Auth::id();
            $payload['status']         = VisitaStatus::Agendada->value;
            $payload['origem']         = VisitaOrigem::Sistema->value;
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

    private function logarErro(array $dados, string $acao, string $mensagemErro): void
    {
        Log::error("Erro ao {$acao} visita!", [
            'sucesso' => false,
            'dados'   => $dados,
            'erros'   => ["Erro ao {$acao} visita: {$mensagemErro}"],
        ]);
    }
}
