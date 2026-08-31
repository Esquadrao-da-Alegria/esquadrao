<?php

namespace App\Services\Hospital\Meta;

// QUERIES
use App\Queries\Hospital\Meta\Queries;

// HELPERS
use App\Helpers\MetaHospital as MetaHospitalHelper;
use App\Helpers\User as UserHelper;
use App\Helpers\Visita as VisitaHelper;

// MODELS
use App\Models\Hospital;
use App\Models\MetaMensalHospital;
use App\Models\MetaSemanalHospital;
use App\Models\User;
use App\Models\Visita;

// LIBS EXTERNAS

// ELOQUENT
use Illuminate\Support\Collection;

// FACADES
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Service
{
    public const META_MENSAL_MAXIMA = 10;

    public const META_SEMANAL_MAXIMA = 5;

    public function __construct(private Queries $queries) {}

    public function index(User $user, Hospital $hospital, array $filtros): array
    {
        $this->validarAcesso($user, $hospital);

        try {
            $ano          = (int) ($filtros['ano'] ?? now()->year);
            $mes          = (int) ($filtros['mes'] ?? now()->month);

            $hospital->load('alas:id,hospital_id,nome');
            $hospitais = collect([$hospital]);

            $metasMensais = $this->buscarMetasMensais($hospitais, $ano, $mes);

            if (! $metasMensais['sucesso']) {
                return $metasMensais;
            }

            $contexto = $this->montarContextoIndex($hospitais, $ano, $mes, $metasMensais['dados']);

            $dadosHospitais = $hospitais
                ->map(fn (Hospital $hospital) => $this->montarHospitalIndex($hospital, $contexto))
                ->values()
                ->all();

            return $this->respostaIndex($ano, $mes, $dadosHospitais);
        } catch (\Throwable $th) {
            $this->logarErro($filtros, 'listar', formatarMensagemErro($th));

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    public function update(User $user, Hospital $hospital, array $dados): array
    {
        $this->validarAcesso($user, $hospital);

        try {
            $ano = (int) $dados['ano'];
            $mes = (int) $dados['mes'];
            $hospitaisPayload = [[
                ...$dados,
                'hospital_id' => $hospital->id,
            ]];

            $erros = $this->validarPayloadUpdate($hospitaisPayload, (int) $hospital->cidade_id, $ano, $mes);

            if ($erros !== []) {
                session()->flash('mensagem_erro', $erros[0]);

                return [
                    'sucesso' => false,
                    'dados'   => [],
                    'erros'   => $erros,
                ];
            }

            DB::beginTransaction();

            foreach ($hospitaisPayload as $hospitalPayload) {
                $falha = $this->salvarMetasDoHospital($hospitalPayload, $ano, $mes);

                if ($falha !== null) {
                    DB::rollBack();

                    return $falha;
                }
            }

            DB::commit();

            session()->flash('mensagem_sucesso', 'Metas salvas com sucesso!');

            return [
                'sucesso' => true,
                'dados'   => [],
                'erros'   => [],
            ];
        } catch (\Throwable $th) {
            DB::rollBack();

            $this->logarErro($dados, 'salvar', formatarMensagemErro($th));

            session()->flash('mensagem_erro', 'Erro ao salvar metas!');

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => [formatarMensagemErro($th)],
            ];
        }
    }

    private function validarAcesso(User $user, Hospital $hospital): void
    {
        if (! UserHelper::ehGestor($user)) {
            abort(403);
        }

        if (! UserHelper::possuiEscopoGlobal($user)
            && (int) $user->voluntario->cidade_base_id !== (int) $hospital->cidade_id) {
            abort(403);
        }

        if (! $hospital->ativo) {
            abort(403);
        }
    }

    /**
     * @return array{sucesso: bool, dados: array<int, array<string, mixed>>, erros: array<int, string>}|null
     */
    private function salvarMetasDoHospital(array $hospitalPayload, int $ano, int $mes): ?array
    {
        $hospitalId = (int) $hospitalPayload['hospital_id'];
        $metaMensal = $this->metaMensalDoPayload($hospitalPayload);

        $this->limparMetasDoHospital($hospitalId, $ano, $mes);

        if ($metaMensal === null) {
            return null;
        }

        $retornoStore = $this->queries->store([
            'hospital_id' => $hospitalId,
            'ano'         => $ano,
            'mes'         => $mes,
            'quantidade'  => $metaMensal,
        ]);

        if (! $retornoStore['sucesso']) {
            session()->flash('mensagem_erro', $retornoStore['erros'][0] ?? 'Erro ao salvar metas!');

            return [
                'sucesso' => false,
                'dados'   => [],
                'erros'   => $retornoStore['erros'],
            ];
        }

        $metasSemanais = $hospitalPayload['metas_semanais'] ?? [];

        if ($metasSemanais === []) {
            return null;
        }

        $this->persistirMetasSemanais(
            $hospitalId,
            $ano,
            $mes,
            $metasSemanais,
            (bool) $hospitalPayload['metas_por_ala'],
        );

        return null;
    }

    private function limparMetasDoHospital(int $hospitalId, int $ano, int $mes): void
    {
        MetaSemanalHospital::query()
            ->where('hospital_id', $hospitalId)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->delete();

        MetaMensalHospital::query()
            ->where('hospital_id', $hospitalId)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $metasSemanais
     */
    private function persistirMetasSemanais(
        int $hospitalId,
        int $ano,
        int $mes,
        array $metasSemanais,
        bool $metasPorAla,
    ): void {
        foreach ($metasSemanais as $metaSemanal) {
            MetaSemanalHospital::create([
                'hospital_id'    => $hospitalId,
                'ala_unidade_id' => $metasPorAla ? (int) $metaSemanal['ala_unidade_id'] : null,
                'ano'            => $ano,
                'mes'            => $mes,
                'semana'         => (int) $metaSemanal['semana'],
                'quantidade'     => (int) $metaSemanal['quantidade'],
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $hospitaisPayload
     * @return array<int, string>
     */
    private function validarPayloadUpdate(array $hospitaisPayload, int $cidadeBaseId, int $ano, int $mes): array
    {
        $erros = [];

        $hospitalIds = array_values(array_unique(array_map(
            fn (array $payload) => (int) $payload['hospital_id'],
            $hospitaisPayload,
        )));

        $hospitais = Hospital::query()
            ->whereIn('id', $hospitalIds)
            ->where('cidade_id', $cidadeBaseId)
            ->where('ativo', true)
            ->with(['alas:id,hospital_id,nome'])
            ->get()
            ->keyBy('id');

        foreach ($hospitaisPayload as $indice => $hospitalPayload) {
            $erros = array_merge(
                $erros,
                $this->validarHospitalPayload($hospitalPayload, $indice, $hospitais, $ano, $mes),
            );
        }

        return $erros;
    }

    /**
     * @param  array<string, mixed>  $hospitalPayload
     * @return array<int, string>
     */
    private function validarHospitalPayload(
        array $hospitalPayload,
        int $indice,
        Collection $hospitais,
        int $ano,
        int $mes,
    ): array {
        $hospitalId = (int) $hospitalPayload['hospital_id'];
        $prefixo    = "hospitais.{$indice}";

        $hospital = $hospitais->get($hospitalId);

        if (! $hospital) {
            return ["{$prefixo}: hospital não pertence à sua cidade-base ou está inativo."];
        }

        $metaMensal    = $this->metaMensalDoPayload($hospitalPayload);
        $metasSemanais = $hospitalPayload['metas_semanais'] ?? [];
        $metasPorAla   = (bool) ($hospitalPayload['metas_por_ala'] ?? false);

        if ($metaMensal === null && $metasSemanais !== []) {
            return ["{$prefixo}: metas semanais exigem meta mensal preenchida."];
        }

        if ($metasSemanais === []) {
            return [];
        }

        return $this->validarMetasSemanaisDoHospital(
            $hospital,
            $metasSemanais,
            $metaMensal,
            $metasPorAla,
            $prefixo,
            $ano,
            $mes,
        );
    }

    /**
     * @param  array<int, array<string, mixed>>  $metasSemanais
     * @return array<int, string>
     */
    private function validarMetasSemanaisDoHospital(
        Hospital $hospital,
        array $metasSemanais,
        ?int $metaMensal,
        bool $metasPorAla,
        string $prefixo,
        int $ano,
        int $mes,
    ): array {
        $erros          = [];
        $somaSemanal    = 0;
        $chavesSemanais = [];

        foreach ($metasSemanais as $metaSemanal) {
            $semana = (int) $metaSemanal['semana'];

            if (! MetaHospitalHelper::semanaValida($ano, $mes, $semana)) {
                return ["{$prefixo}: semana {$semana} inválida para o mês informado."];
            }

            $alaUnidadeId = $metaSemanal['ala_unidade_id'] ?? null;
            $chaveSemanal = $metasPorAla
                ? "{$semana}-{$alaUnidadeId}"
                : (string) $semana;

            if (isset($chavesSemanais[$chaveSemanal])) {
                return ["{$prefixo}: meta semanal duplicada para a mesma semana."];
            }

            $chavesSemanais[$chaveSemanal] = true;
            $somaSemanal                  += (int) $metaSemanal['quantidade'];

            $erros = array_merge(
                $erros,
                $this->validarAlaDaMetaSemanal($hospital, $metasPorAla, $alaUnidadeId, $prefixo),
            );
        }

        if ($metaMensal !== null && $somaSemanal !== $metaMensal) {
            $erros[] = "{$prefixo}: soma das metas semanais ({$somaSemanal}) difere da meta mensal ({$metaMensal}).";
        }

        return $erros;
    }

    /**
     * @return array<int, string>
     */
    private function validarAlaDaMetaSemanal(
        Hospital $hospital,
        bool $metasPorAla,
        mixed $alaUnidadeId,
        string $prefixo,
    ): array {
        if ($metasPorAla) {
            if ($alaUnidadeId === null) {
                return ["{$prefixo}: meta semanal por ala exige ala_unidade_id."];
            }

            if (! $hospital->alas->contains('id', (int) $alaUnidadeId)) {
                return ["{$prefixo}: ala {$alaUnidadeId} não pertence ao hospital."];
            }

            return [];
        }

        if ($alaUnidadeId !== null) {
            return ["{$prefixo}: meta semanal por hospital não pode informar ala."];
        }

        return [];
    }

    /**
     * @param  Collection<int, Hospital>  $hospitais
     * @return array{sucesso: bool, dados: mixed, erros: array<int, string>}
     */
    private function buscarMetasMensais(Collection $hospitais, int $ano, int $mes): array
    {
        return $this->queries->index([
            'retornar_lista' => true,
            'hospital_ids'   => $hospitais->pluck('id')->all(),
            'ano'            => $ano,
            'mes'            => $mes,
        ]);
    }

    /**
     * @param  Collection<int, Hospital>  $hospitais
     * @param  Collection<int, MetaMensalHospital>  $metasMensais
     * @return array{
     *     ano: int,
     *     mes: int,
     *     semanas: array<int, int>,
     *     metas_mensais: Collection<int|string, MetaMensalHospital>,
     *     metas_semanais: Collection<int|string, Collection<int, MetaSemanalHospital>>,
     *     realizadas: Collection<int, object>
     * }
     */
    private function montarContextoIndex(Collection $hospitais, int $ano, int $mes, Collection $metasMensais): array
    {
        $hospitalIds = $hospitais->pluck('id')->all();

        $metasSemanais = MetaSemanalHospital::query()
            ->whereIn('hospital_id', $hospitalIds)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->get();

        return [
            'ano'            => $ano,
            'mes'            => $mes,
            'semanas'        => MetaHospitalHelper::numerosSemanasDoMes($ano, $mes),
            'metas_mensais'  => $metasMensais->keyBy('hospital_id'),
            'metas_semanais' => $metasSemanais->groupBy('hospital_id'),
            'realizadas'     => $this->buscarRealizadasAgregadas($hospitalIds, $ano, $mes),
        ];
    }

    /**
     * @param  array{
     *     ano: int,
     *     mes: int,
     *     semanas: array<int, int>,
     *     metas_mensais: Collection<int|string, MetaMensalHospital>,
     *     metas_semanais: Collection<int|string, Collection<int, MetaSemanalHospital>>,
     *     realizadas: Collection<int, object>
     * }  $contexto
     * @return array<string, mixed>
     */
    private function montarHospitalIndex(Hospital $hospital, array $contexto): array
    {
        $metasSemanaisHospital = $contexto['metas_semanais']->get($hospital->id, collect());
        $metasPorAla           = $this->usaMetasPorAla($metasSemanaisHospital);
        $metaMensal            = $contexto['metas_mensais']->get($hospital->id);

        return [
            'id'                => (int) $hospital->id,
            'nome'              => $hospital->nome,
            'alas'              => $this->formatarAlas($hospital),
            'meta_mensal'       => $metaMensal ? (int) $metaMensal->quantidade : null,
            'realizadas_mensal' => $this->realizadasMensais(
                $contexto['realizadas'],
                (int) $hospital->id,
                $metasPorAla,
            ),
            'metas_por_ala'  => $metasPorAla,
            'metas_semanais' => $this->montarMetasSemanais(
                $hospital,
                $metasSemanaisHospital,
                $contexto['realizadas'],
                $metasPorAla,
                $contexto['semanas'],
            ),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $hospitais
     * @return array{sucesso: true, dados: array{ano: int, mes: int, hospitais: array<int, array<string, mixed>>}, erros: array<int, string>}
     */
    private function respostaIndex(int $ano, int $mes, array $hospitais): array
    {
        return [
            'sucesso' => true,
            'dados'   => [
                'ano'       => $ano,
                'mes'       => $mes,
                'semanas'   => MetaHospitalHelper::semanasDoMes($ano, $mes),
                'hospitais' => $hospitais,
            ],
            'erros' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $hospitalPayload
     */
    private function metaMensalDoPayload(array $hospitalPayload): ?int
    {
        if (! array_key_exists('meta_mensal', $hospitalPayload) || $hospitalPayload['meta_mensal'] === null) {
            return null;
        }

        return (int) $hospitalPayload['meta_mensal'];
    }

    /**
     * @param  Collection<int, MetaSemanalHospital>  $metasSemanaisHospital
     */
    private function usaMetasPorAla(Collection $metasSemanaisHospital): bool
    {
        return $metasSemanaisHospital->contains(fn ($meta) => $meta->ala_unidade_id !== null);
    }

    /**
     * @return array<int, array{id: int, nome: string}>
     */
    private function formatarAlas(Hospital $hospital): array
    {
        return $hospital->alas
            ->map(fn ($ala) => [
                'id'   => (int) $ala->id,
                'nome' => $ala->nome,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, int|string>  $hospitalIds
     */
    private function buscarRealizadasAgregadas(array $hospitalIds, int $ano, int $mes): Collection
    {
        $expressaoDia = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%d', inicio_em) AS INTEGER)"
            : 'DAY(inicio_em)';
        $sqlSemana = MetaHospitalHelper::sqlSemanaVisita($ano, $mes, $expressaoDia);

        return Visita::query()
            ->select([
                'hospital_id',
                'ala_unidade_id',
                DB::raw("{$sqlSemana} as semana"),
                DB::raw('COUNT(*) as total'),
            ])
            ->whereIn('hospital_id', $hospitalIds)
            ->whereYear('inicio_em', $ano)
            ->whereMonth('inicio_em', $mes)
            ->whereIn('status', VisitaHelper::statusRealizadasValores())
            ->groupBy('hospital_id', 'ala_unidade_id')
            ->groupByRaw($sqlSemana)
            ->get();
    }

    private function realizadasMensais(Collection $realizadas, int $hospitalId, bool $metasPorAla): int
    {
        $registros = $realizadas->where('hospital_id', $hospitalId);

        if ($metasPorAla) {
            return (int) $registros
                ->whereNull('ala_unidade_id')
                ->sum('total');
        }

        return (int) $registros->sum('total');
    }

    /**
     * @param  Collection<int, MetaSemanalHospital>  $metasSemanaisHospital
     * @param  array<int, int>  $semanasDoMes
     * @return array<int, array<string, int|null>>
     */
    private function montarMetasSemanais(
        Hospital $hospital,
        Collection $metasSemanaisHospital,
        Collection $realizadas,
        bool $metasPorAla,
        array $semanasDoMes,
    ): array {
        if ($metasPorAla) {
            return $this->montarMetasSemanaisPorAla(
                $hospital,
                $metasSemanaisHospital,
                $realizadas,
                $semanasDoMes,
            );
        }

        return $this->montarMetasSemanaisPorHospital(
            $hospital,
            $metasSemanaisHospital,
            $realizadas,
            $semanasDoMes,
        );
    }

    /**
     * @param  Collection<int, MetaSemanalHospital>  $metasSemanaisHospital
     * @param  array<int, int>  $semanasDoMes
     * @return array<int, array<string, int|null>>
     */
    private function montarMetasSemanaisPorAla(
        Hospital $hospital,
        Collection $metasSemanaisHospital,
        Collection $realizadas,
        array $semanasDoMes,
    ): array {
        $resultado = [];

        foreach ($hospital->alas as $ala) {
            foreach ($semanasDoMes as $semana) {
                $resultado[] = [
                    'semana'         => $semana,
                    'ala_unidade_id' => (int) $ala->id,
                    'meta'           => $this->quantidadeMetaSemanal($metasSemanaisHospital, $semana, (int) $ala->id),
                    'realizadas'     => $this->realizadasSemanais($realizadas, (int) $hospital->id, $semana, (int) $ala->id),
                ];
            }
        }

        return $resultado;
    }

    /**
     * @param  Collection<int, MetaSemanalHospital>  $metasSemanaisHospital
     * @param  array<int, int>  $semanasDoMes
     * @return array<int, array<string, int|null>>
     */
    private function montarMetasSemanaisPorHospital(
        Hospital $hospital,
        Collection $metasSemanaisHospital,
        Collection $realizadas,
        array $semanasDoMes,
    ): array {
        return collect($semanasDoMes)
            ->map(fn (int $semana) => [
                'semana'     => $semana,
                'meta'       => $this->quantidadeMetaSemanal($metasSemanaisHospital, $semana),
                'realizadas' => $this->realizadasSemanais($realizadas, (int) $hospital->id, $semana),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, MetaSemanalHospital>  $metasSemanaisHospital
     */
    private function quantidadeMetaSemanal(Collection $metasSemanaisHospital, int $semana, ?int $alaId = null): ?int
    {
        $meta = $metasSemanaisHospital->first(function ($item) use ($semana, $alaId) {
            if ((int) $item->semana !== $semana) {
                return false;
            }

            if ($alaId === null) {
                return $item->ala_unidade_id === null;
            }

            return (int) $item->ala_unidade_id === $alaId;
        });

        return $meta ? (int) $meta->quantidade : null;
    }

    private function realizadasSemanais(
        Collection $realizadas,
        int $hospitalId,
        int $semana,
        ?int $alaId = null,
    ): int {
        $query = $realizadas
            ->where('hospital_id', $hospitalId)
            ->where('semana', $semana);

        if ($alaId !== null) {
            $query = $query->where('ala_unidade_id', $alaId);
        }

        return (int) $query->sum('total');
    }

    private function logarErro(array $dados, string $acao, string $mensagemErro): void
    {
        $mensagem = "Erro ao {$acao} metas hospitalares!";

        Log::error($mensagem, [
            'sucesso' => false,
            'dados'   => $dados,
            'erros'   => ["{$mensagem}: {$mensagemErro}"],
        ]);
    }
}
