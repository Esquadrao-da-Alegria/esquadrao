<?php

namespace App\Services\Dashboard;

use App\Models\User;
use App\Queries\Dashboard\Queries;
use App\Services\Dashboard\Visita\Participante\Meta\Service as MetaService;
use App\Services\Visita\Relatorio\Prazo\Service as PrazoService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class Service
{
    public function __construct(
        private Queries $queries,
        private MetaService $metaService,
    ) {}

    public function index(User $user): array
    {
        resolverUsuario($user);
        $agora = now();
        $inicioSemestre = $agora->month <= 6
            ? $agora->copy()->startOfYear()
            : $agora->copy()->startOfYear()->addMonths(6);
        $dados = $this->queries->index($user->id, $agora->copy()->startOfMonth(), $inicioSemestre, $inicioSemestre->copy()->addMonths(6)->subSecond());
        $atividades = $this->atividades($dados['proximasVisitas'], $dados['proximosEventos']);
        $pendencias = $this->pendencias($dados['relatoriosPendentes']);
        $tipoAtuacao = $this->metaService->tipo($user);
        $visitasValidas = $dados['visitasMes']->where('valida', 1)->count();

        return [
            'contexto' => [
                'nome' => $user->voluntario?->nome_completo ?? $user->name,
                'cidade' => $user->voluntario?->cidadeBase?->nome,
                'possui_vinculo' => (bool) $user->voluntario,
                'mensagem' => $this->mensagem($user, $atividades, $pendencias),
            ],
            'proximas_atividades' => $atividades,
            'pendencias' => $pendencias,
            'avisos' => $this->avisos($user->voluntario?->cidade_base_id),
            'resumo' => [
                'visitas_validas_mes' => $user->voluntario ? $visitasValidas : null,
                'oficinas_semestre' => $user->voluntario ? $dados['eventosSemestre']->where('tipo', 'oficina')->count() : null,
                'reunioes_semestre' => $user->voluntario ? $dados['eventosSemestre']->where('tipo', 'reuniao')->count() : null,
                'meta' => $this->meta($tipoAtuacao, $visitasValidas),
            ],
        ];
    }

    private function atividades(object $visitas, object $eventos): array
    {
        return collect($visitas)->map(fn ($visita) => [
            'id' => (int) $visita->id,
            'categoria' => 'visita',
            'titulo' => 'Visita '.str($visita->tipo)->replace('_', ' ')->title(),
            'inicio_em' => Carbon::parse($visita->inicio_em)->toISOString(),
            'fim_em' => Carbon::parse($visita->fim_em)->toISOString(),
            'local' => $visita->local ?? 'Local não informado',
            'cidade' => $visita->cidade,
            'situacao' => 'Confirmada',
            'detalhes_url' => route('visitas.index', ['mes' => Carbon::parse($visita->inicio_em)->format('Y-m'), 'visita_id' => $visita->id]),
        ])->concat(collect($eventos)->map(fn ($evento) => [
            'id' => (int) $evento->id,
            'categoria' => $evento->tipo,
            'titulo' => $evento->titulo,
            'inicio_em' => Carbon::parse($evento->inicio_em)->toISOString(),
            'fim_em' => Carbon::parse($evento->fim_em)->toISOString(),
            'local' => $evento->local ?: 'Local não informado',
            'cidade' => $evento->cidade,
            'situacao' => 'Inscrição confirmada',
            'detalhes_url' => route('eventos.show', $evento->id),
        ]))->sortBy('inicio_em')->take(6)->values()->all();
    }

    private function pendencias(object $visitas): array
    {
        return collect($visitas)->map(function ($visita) {
            $prazo = Carbon::parse($visita->fim_em)->addHours(PrazoService::HORAS);
            $estado = $prazo->isPast() ? 'atrasado' : ($prazo->diffInHours(now()) <= 12 ? 'prazo_proximo' : 'em_prazo');

            return [
                'id' => 'relatorio-'.$visita->id,
                'tipo' => 'relatorio',
                'titulo' => 'Relatório de visita pendente',
                'descricao' => collect([$visita->local, $visita->cidade])->filter()->join(' — '),
                'prazo_em' => $prazo->toISOString(),
                'estado_prazo' => $estado,
                'acao' => ['titulo' => 'Cadastrar relatório', 'url' => route('visitas.relatorios.create', $visita->id)],
            ];
        })->all();
    }

    private function avisos(?int $cidadeId): array
    {
        try {
            return collect(config('dashboard.avisos', []))
                ->filter(fn (array $aviso) => empty($aviso['cidade_id']) || (int) $aviso['cidade_id'] === $cidadeId)
                ->filter(fn (array $aviso) => empty($aviso['publicado_em']) || Carbon::parse($aviso['publicado_em'])->lte(now()))
                ->filter(fn (array $aviso) => empty($aviso['expira_em']) || Carbon::parse($aviso['expira_em'])->gte(now()))
                ->sortByDesc('prioridade')->take(3)
                ->map(fn (array $aviso) => collect($aviso)->only(['id', 'titulo', 'mensagem', 'tipo', 'link'])->all())
                ->values()->all();
        } catch (\Throwable $throwable) {
            Log::warning('Não foi possível carregar os avisos da visão geral.', ['erro' => $throwable->getMessage()]);
            return [];
        }
    }

    private function mensagem(User $user, array $atividades, array $pendencias): string
    {
        if (! $user->voluntario) return 'Esta conta não possui perfil de voluntário vinculado. Use os atalhos disponíveis para continuar.';
        if ($pendencias) return 'Você tem uma pendência que precisa de atenção.';
        if ($atividades) return 'Confira sua agenda e prepare-se para a próxima atividade.';
        return 'Não há ações imediatas. Você pode consultar as atividades disponíveis.';
    }

    private function meta(string $tipoAtuacao, int $visitasValidas): array
    {
        return match ($tipoAtuacao) {
            'isento' => ['situacao' => 'isento', 'atual' => null, 'objetivo' => null],
            'visitas' => ['situacao' => 'aplicavel', 'atual' => $visitasValidas, 'objetivo' => MetaService::META_VISITAS],
            default => ['situacao' => 'dados_insuficientes', 'atual' => null, 'objetivo' => null],
        };
    }
}
