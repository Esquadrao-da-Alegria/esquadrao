<?php

namespace App\Services\Visita\Agenda\Liberacao;

// QUERIES
use App\Queries\Visita\Agenda\Liberacao\Queries;

// HELPERS
use App\Helpers\User as UserHelper;

// MODELS
use App\Models\AgendaLiberacaoCidade;
use App\Models\Cidade;
use App\Models\User;

// FACADES
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __construct(private Queries $queries) {}

    public function index(User $user, array $filtros): array
    {
        try {
            $cidadeBaseId = $this->validarAcesso($user);
            $ano          = (int) ($filtros['ano'] ?? now()->year);

            $liberacoes = $this->buscarLiberacoesDoAno($cidadeBaseId, $ano);

            if (! $liberacoes['sucesso']) {
                return $liberacoes;
            }

            $liberacoesPorMes = $liberacoes['dados']->keyBy('mes');

            return [
                'sucesso' => true,
                'dados'   => [
                    'ano'   => $ano,
                    'meses' => $this->montarMesesDoAno($ano, $liberacoesPorMes),
                ],
                'erros' => [],
            ];
        } catch (\Throwable $th) {
            $this->logarErro($filtros, 'listar', formatarMensagemErro($th));

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function update(User $user, array $dados): array
    {
        $this->validarAcesso($user);
        $cidadeId = (int) $dados['cidade_id'];
        $this->validarCidade($user, $cidadeId);

        try {
            $ano          = (int) $dados['ano'];
            $mes          = (int) $dados['mes'];
            $liberado     = (bool) $dados['liberado'];

            if (! $this->mesPermiteAlteracao($ano, $mes)) {
                session()->flash('mensagem_erro', 'Não é possível alterar meses passados.');

                return [
                    'sucesso' => false,
                    'dados'   => [],
                    'erros'   => ['Não é possível alterar meses passados.'],
                ];
            }

            DB::beginTransaction();

            $falha = $this->persistirLiberacao($cidadeId, $ano, $mes, $liberado, $user->id);

            if ($falha !== null) {
                DB::rollBack();

                return $falha;
            }

            DB::commit();

            session()->flash(
                'mensagem_sucesso',
                $liberado ? 'Agenda liberada com sucesso!' : 'Agenda bloqueada com sucesso!',
            );

            return [
                'sucesso' => true,
                'dados'   => [],
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logarErro($dados, 'salvar', formatarMensagemErro($th));

            session()->flash('mensagem_erro', 'Erro ao atualizar liberação da agenda!');

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function mesEstaLiberado(int $cidadeId, int $ano, int $mes): bool
    {
        $registro = AgendaLiberacaoCidade::query()
            ->where('cidade_id', $cidadeId)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->first();

        return $registro?->liberado === true;
    }

    public function situacao(User $user, int $cidadeId, int $ano, int $mes): array
    {
        $this->validarAcesso($user);
        $this->validarCidade($user, $cidadeId);

        return [
            'liberado' => $this->mesEstaLiberado($cidadeId, $ano, $mes),
            'editavel' => $this->mesPermiteAlteracao($ano, $mes),
        ];
    }

    public function situacaoConsulta(int $cidadeId, int $ano, int $mes): array
    {
        return [
            'liberado' => $this->mesEstaLiberado($cidadeId, $ano, $mes),
            'editavel' => $this->mesPermiteAlteracao($ano, $mes),
        ];
    }

    public function podeGerenciarCidade(User $user, int $cidadeId): bool
    {
        if (! UserHelper::ehGestor($user)) {
            return false;
        }

        return UserHelper::possuiEscopoGlobal($user)
            || (int) $user->voluntario?->cidade_base_id === $cidadeId;
    }

    /**
     * @return array<int, string>
     */
    public function listarMesesLiberados(int $cidadeId): array
    {
        return AgendaLiberacaoCidade::query()
            ->where('cidade_id', $cidadeId)
            ->where('liberado', true)
            ->orderBy('ano')
            ->orderBy('mes')
            ->get(['ano', 'mes'])
            ->map(fn (AgendaLiberacaoCidade $registro) => sprintf('%04d-%02d', $registro->ano, $registro->mes))
            ->values()
            ->all();
    }

    private function validarAcesso(User $user): ?int
    {
        if (! UserHelper::ehGestor($user)) {
            abort(403);
        }

        return $user->voluntario?->cidade_base_id
            ? (int) $user->voluntario->cidade_base_id
            : null;
    }

    private function validarCidade(User $user, int $cidadeId): void
    {
        if (! Cidade::query()->whereKey($cidadeId)->exists()) {
            abort(404);
        }

        if (! UserHelper::possuiEscopoGlobal($user)
            && (int) $user->voluntario->cidade_base_id !== $cidadeId) {
            abort(403);
        }
    }

    /**
     * @return array{sucesso: bool, dados: mixed, erros: array<int, string>}
     */
    private function buscarLiberacoesDoAno(int $cidadeBaseId, int $ano): array
    {
        return $this->queries->index([
            'retornar_lista' => true,
            'cidade_id'      => $cidadeBaseId,
            'ano'            => $ano,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int|string, AgendaLiberacaoCidade>  $liberacoesPorMes
     * @return array<int, array{mes: int, liberado: bool, editavel: bool}>
     */
    private function montarMesesDoAno(int $ano, $liberacoesPorMes): array
    {
        $meses = [];

        for ($mes = 1; $mes <= 12; $mes++) {
            $registro = $liberacoesPorMes->get($mes);

            $meses[] = [
                'mes'       => $mes,
                'liberado'  => $registro?->liberado === true,
                'editavel'  => $this->mesPermiteAlteracao($ano, $mes),
            ];
        }

        return $meses;
    }

    private function mesPermiteAlteracao(int $ano, int $mes): bool
    {
        $agora = now();

        if ($ano < (int) $agora->year) {
            return false;
        }

        if ($ano > (int) $agora->year) {
            return true;
        }

        return $mes >= (int) $agora->month;
    }

    /**
     * @return array{sucesso: false, dados: array<int, mixed>, erros: array<int, string>}|null
     */
    private function persistirLiberacao(
        int $cidadeBaseId,
        int $ano,
        int $mes,
        bool $liberado,
        int $usuarioId,
    ): ?array {
        $registro = AgendaLiberacaoCidade::query()
            ->where('cidade_id', $cidadeBaseId)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->first();

        $payload = [
            'liberado'        => $liberado,
            'liberado_por_id' => $liberado ? $usuarioId : null,
        ];

        if ($registro) {
            $retorno = $this->queries->update($registro->id, $payload);
        } else {
            $retorno = $this->queries->store([
                'cidade_id'       => $cidadeBaseId,
                'ano'             => $ano,
                'mes'             => $mes,
                ...$payload,
            ]);
        }

        if (! $retorno['sucesso']) {
            session()->flash('mensagem_erro', $retorno['erros'][0] ?? 'Erro ao atualizar liberação da agenda!');

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => $retorno['erros'],
            ];
        }

        return null;
    }

    private function logarErro(array $dados, string $acao, string $mensagemErro): void
    {
        $mensagem = "Erro ao {$acao} liberação de agenda!";

        Log::error($mensagem, [
            'sucesso' => false,
            'dados'   => $dados,
            'erros'   => ["{$mensagem}: {$mensagemErro}"],
        ]);
    }
}
