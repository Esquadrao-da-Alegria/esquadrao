<?php

namespace App\Services\Dashboard\Meu;

use App\Enums\StatusParticipacao;
use App\Enums\VisitaStatus;
use App\Models\User;
use App\Queries\Dashboard\Meu\Queries;
use App\Services\Dashboard\Visita\Participante\Compensacao\Service as CompensacaoService;
use App\Services\Dashboard\Visita\Participante\Meta\Service as MetaService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Service
{
    public function __construct(
        private Queries $queries,
        private MetaService $metaService,
        private CompensacaoService $compensacaoService,
    ) {}

    public function index(User $user, array $filtros): array
    {
        $user->loadMissing(['cargos', 'voluntario.cidadeBase']);

        $filtros = $this->normalizarFiltros($user, $filtros);
        $dados = $this->queries->index($user->id, $filtros);
        $tipoAtuacao = $this->metaService->tipo($user);
        $visitas = $dados['visitas'];
        $visitasRealizadas = $visitas->filter(fn ($visita) => $this->realizada($visita));
        $visitasValidas = $visitas->filter(fn ($visita) => $this->valida($visita));
        $compensacoes = $tipoAtuacao === 'visitas'
            ? $this->compensacoes($dados['visitasCalculo'], $filtros)
            : [];
        $presencas = $this->presencas($dados['eventos'], $dados['eventosOferecidos']);
        $historico = $this->historico($visitas, $dados['eventos'], $filtros['atividade']);
        $ultimaVisita = $visitasValidas->sortByDesc('inicio_em')->first();
        $saldo = $compensacoes ? $compensacoes[array_key_last($compensacoes)]['saldo'] : null;
        $compensacaoAtual = $compensacoes ? $compensacoes[array_key_last($compensacoes)]['situacao'] : null;

        return [
            'voluntario' => [
                'nome' => $user->voluntario?->nome_completo ?? $user->name,
                'cidade' => $user->voluntario?->cidadeBase?->nome ?? 'Sem voluntário vinculado',
                'cargos' => $user->cargos->pluck('nome')->values(),
                'tipo_atuacao' => $tipoAtuacao,
                'possui_vinculo' => (bool) $user->voluntario,
            ],
            'indicadores' => [
                'visitas_validas' => $visitasValidas->count(),
                'visitas_realizadas' => $visitasRealizadas->count(),
                'meta_mensal' => $tipoAtuacao === 'visitas' ? MetaService::META_VISITAS : null,
                'saldo' => $saldo,
                'compensacao' => $compensacaoAtual,
                'reunioes' => $presencas['reuniao'],
                'oficinas' => $presencas['oficina'],
                'impacto_estimado' => round($visitasRealizadas->sum('impacto_estimado')),
                'hospitais_visitados' => $visitasValidas->pluck('local')->unique()->count(),
                'ultima_visita_valida' => $ultimaVisita?->inicio_em,
                'relatorios_pendentes' => $visitasRealizadas->where('possui_relatorio', 0)->count(),
                'relatorios_fora_prazo' => $visitasRealizadas->where('possui_relatorio', 1)->where('possui_relatorio_no_prazo', 0)->count(),
            ],
            'evolucao' => $this->evolucao($visitas, $filtros, $tipoAtuacao),
            'compensacoes' => $compensacoes,
            'presencas' => $this->atividadesConsideradas($dados['eventosOferecidos'], $dados['eventos']),
            'hospitais' => $visitasValidas->groupBy('local')->map(fn ($itens, $nome) => ['nome' => $nome, 'total' => $itens->count()])->sortByDesc('total')->take(5)->values(),
            'companheiros' => $this->queries->companheiros($visitasValidas->pluck('id')->all(), $user->id),
            'cidades' => $visitasRealizadas->groupBy('cidade')->map(fn ($itens, $nome) => ['nome' => $nome, 'total' => $itens->count()])->sortByDesc('total')->values(),
            'historico' => $this->paginar($historico),
            'proximas_atividades' => $this->proximasAtividades($dados['proximasVisitas'], $dados['proximosEventos']),
            'orientacoes' => $this->orientacoes(
                $tipoAtuacao,
                $compensacoes ? $compensacoes[array_key_last($compensacoes)]['visitas'] : $visitasValidas->count(),
                $saldo,
                $presencas
            ),
            'filtros' => collect($filtros)->except(['inicio', 'fim', 'cidade_base_id'])->all(),
            'opcoes' => ['cidades' => $dados['cidades']],
        ];
    }

    private function normalizarFiltros(User $user, array $filtros): array
    {
        $tipo = $filtros['periodo_tipo'];

        if ($tipo === 'personalizado') {
            $inicio = Carbon::parse($filtros['data_inicio'])->startOfDay();
            $fim = Carbon::parse($filtros['data_fim'])->endOfDay();
        } elseif ($tipo === 'semestre') {
            $inicio = Carbon::create((int) $filtros['ano'], (int) $filtros['semestre'] === 1 ? 1 : 7)->startOfMonth();
            $fim = $inicio->copy()->addMonths(5)->endOfMonth();
        } elseif ($tipo === 'ano') {
            $inicio = Carbon::create((int) $filtros['ano'])->startOfYear();
            $fim = $inicio->copy()->endOfYear();
        } else {
            $inicio = Carbon::create((int) $filtros['ano'], (int) $filtros['mes'])->startOfMonth();
            $fim = $inicio->copy()->endOfMonth();
        }

        return [
            ...$filtros,
            'cidade_id' => isset($filtros['cidade_id']) ? (int) $filtros['cidade_id'] : null,
            'atividade' => $filtros['atividade'] ?? null,
            'cidade_base_id' => $user->voluntario?->cidade_base_id,
            'inicio' => $inicio,
            'fim' => $fim,
        ];
    }

    private function realizada(object $visita): bool
    {
        return $visita->status !== VisitaStatus::Cancelada->value
            && $visita->status !== VisitaStatus::Agendada->value
            && $visita->status_participacao === StatusParticipacao::Confirmado->value;
    }

    private function valida(object $visita): bool
    {
        return $this->realizada($visita) && (bool) $visita->possui_relatorio_no_prazo;
    }

    private function compensacoes(Collection $visitas, array $filtros): array
    {
        $porMes = $visitas->filter(fn ($visita) => $this->valida($visita))
            ->groupBy(fn ($visita) => substr($visita->inicio_em, 0, 7))->map->count()->all();
        $meses = collect(CarbonPeriod::create($filtros['inicio']->copy()->subMonth()->startOfMonth(), '1 month', $filtros['fim']->copy()->startOfMonth()))
            ->map(fn (Carbon $mes) => $mes->format('Y-m'))->all();

        return array_slice($this->compensacaoService->calcular($porMes, $meses), 1);
    }

    private function presencas(Collection $participacoes, Collection $oferecidos): array
    {
        $resultado = [];
        foreach (['reuniao', 'oficina'] as $tipo) {
            $eventos = $oferecidos->where('tipo', $tipo);
            $presentes = $participacoes->where('tipo', $tipo)->where('presenca', 'presente')->whereIn('id', $eventos->pluck('id'))->count();
            $incompleto = $eventos->contains(fn ($evento) => (bool) $evento->presencas_incompletas);
            $resultado[$tipo] = [
                'oferecidos' => $eventos->count(),
                'presencas' => $presentes,
                'percentual' => $eventos->isNotEmpty() && ! $incompleto ? round($presentes / $eventos->count() * 100, 1) : null,
                'dados_incompletos' => $eventos->isEmpty() || $incompleto,
            ];
        }
        return $resultado;
    }

    private function evolucao(Collection $visitas, array $filtros, string $tipoAtuacao): Collection
    {
        $realizadas = $visitas->filter(fn ($visita) => $this->realizada($visita))->groupBy(fn ($visita) => substr($visita->inicio_em, 0, 7));
        return collect(CarbonPeriod::create($filtros['inicio']->copy()->startOfMonth(), '1 month', $filtros['fim']->copy()->startOfMonth()))->map(function (Carbon $mes) use ($realizadas, $tipoAtuacao) {
            $itens = $realizadas->get($mes->format('Y-m'), collect());
            return ['mes' => $mes->format('Y-m'), 'rotulo' => $mes->translatedFormat('M/y'), 'validas' => $itens->filter(fn ($visita) => $this->valida($visita))->count(), 'nao_contabilizadas' => $itens->reject(fn ($visita) => $this->valida($visita))->count(), 'meta' => $tipoAtuacao === 'visitas' ? MetaService::META_VISITAS : null];
        })->values();
    }

    private function atividadesConsideradas(Collection $oferecidos, Collection $participacoes): Collection
    {
        return $oferecidos->map(function ($evento) use ($participacoes) {
            $participacao = $participacoes->firstWhere('id', $evento->id);
            return ['id' => (int) $evento->id, 'tipo' => $evento->tipo, 'titulo' => $evento->titulo, 'local' => $evento->local, 'data' => $evento->data_inicio, 'presenca' => $participacao?->presenca, 'considerado' => ! (bool) $evento->presencas_incompletas, 'motivo' => (bool) $evento->presencas_incompletas ? 'Existem presenças ainda não registradas' : 'Evento finalizado da sua cidade-base'];
        })->values();
    }

    private function historico(Collection $visitas, Collection $eventos, ?string $atividade): Collection
    {
        $historicoVisitas = $visitas->map(fn ($visita) => [
            'id' => (int) $visita->id, 'tipo' => 'visita', 'titulo' => 'Visita hospitalar', 'data' => $visita->inicio_em,
            'local' => $visita->local, 'cidade' => $visita->cidade, 'ala' => $visita->ala ?? 'Sem ala informada',
            'tipo_participacao' => $visita->tipo_participacao, 'situacao' => $this->valida($visita) ? 'contabilizada' : 'nao_contabilizada',
            'motivo' => $this->motivo($visita), 'relatorio' => ! $visita->possui_relatorio ? 'pendente' : ($visita->possui_relatorio_no_prazo ? 'enviado' : 'fora_do_prazo'),
            'impacto_estimado' => $visita->impacto_estimado !== null ? round((float) $visita->impacto_estimado) : null,
        ]);
        $historicoEventos = $eventos->map(fn ($evento) => [
            'id' => (int) $evento->id, 'tipo' => $evento->tipo, 'titulo' => $evento->titulo, 'data' => $evento->data_inicio,
            'local' => $evento->local ?? 'Local não informado', 'cidade' => $evento->cidade ?? 'Sem cidade', 'ala' => null,
            'tipo_participacao' => null, 'situacao' => $evento->presenca === 'presente' ? 'presente' : ($evento->presenca ?? 'não informada'),
            'motivo' => $evento->status === 'finalizado' ? 'Evento finalizado' : 'Evento ainda não finalizado', 'relatorio' => null, 'impacto_estimado' => null,
        ]);

        if ($atividade === 'visitas') return $historicoVisitas->sortByDesc('data')->values();
        if ($atividade === 'reunioes') return $historicoEventos->where('tipo', 'reuniao')->sortByDesc('data')->values();
        if ($atividade === 'oficinas') return $historicoEventos->where('tipo', 'oficina')->sortByDesc('data')->values();
        return $historicoVisitas->concat($historicoEventos)->sortByDesc('data')->values();
    }

    private function motivo(object $visita): string
    {
        if ($visita->status === VisitaStatus::Cancelada->value) return 'Visita cancelada';
        if ($visita->status_participacao !== StatusParticipacao::Confirmado->value) return 'Participação não confirmada';
        if (! $visita->possui_relatorio) return 'Seu relatório ainda não foi enviado';
        if (! $visita->possui_relatorio_no_prazo) return 'Seu relatório foi enviado fora do prazo';
        return 'Visita válida no período';
    }

    private function paginar(Collection $itens): LengthAwarePaginator
    {
        $pagina = LengthAwarePaginator::resolveCurrentPage();
        return new LengthAwarePaginator($itens->forPage($pagina, 12)->values(), $itens->count(), 12, $pagina, ['path' => request()->url(), 'query' => request()->query()]);
    }

    private function proximasAtividades(Collection $visitas, Collection $eventos): Collection
    {
        return $visitas->map(fn ($visita) => ['id' => (int) $visita->id, 'tipo' => 'visita', 'titulo' => $visita->local, 'local' => $visita->ala ?? 'Sem ala informada', 'cidade' => $visita->cidade, 'data' => $visita->inicio_em, 'relatorio_pendente' => false])
            ->concat($eventos->map(fn ($evento) => ['id' => (int) $evento->id, 'tipo' => $evento->tipo, 'titulo' => $evento->titulo, 'local' => $evento->local ?? 'Local não informado', 'cidade' => $evento->cidade ?? 'Sem cidade', 'data' => $evento->data_inicio, 'relatorio_pendente' => false]))
            ->sortBy('data')->take(6)->values();
    }

    private function orientacoes(string $tipo, int $visitas, ?int $saldo, array $presencas): array
    {
        if ($tipo === 'administrativo') return ['Sua atuação é administrativa. A regra institucional é de 8 horas mensais, mas o registro confiável de horas ainda não está disponível.'];
        if ($tipo === 'isento') return ['A meta de visitas não se aplica ao seu cargo de apoio. Suas atividades documentadas continuam disponíveis no histórico.'];
        if ($tipo === 'dados_insuficientes') return ['Ainda não há dados suficientes para identificar a meta aplicável. Converse com sua coordenação se precisar de orientação.'];

        $mensagens = [$visitas >= MetaService::META_VISITAS ? 'Você está dentro da meta de visitas do período selecionado.' : "Você realizou {$visitas} de ".MetaService::META_VISITAS.' visitas previstas. Ainda é possível completar a meta.'];
        if ($saldo !== null && $saldo < 0) $mensagens[] = 'Existe um saldo a compensar. Consulte a evolução mensal para entender o cálculo.';
        foreach (['reuniao' => 'reuniões', 'oficina' => 'oficinas'] as $tipoEvento => $rotulo) {
            $percentual = $presencas[$tipoEvento]['percentual'];
            if ($percentual !== null && $percentual < MetaService::META_PRESENCA) $mensagens[] = "Sua presença em {$rotulo} está abaixo de 70%. Confira as atividades consideradas.";
        }
        return $mensagens;
    }
}
