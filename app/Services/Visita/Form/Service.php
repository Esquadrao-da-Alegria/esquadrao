<?php

namespace App\Services\Visita\Form;

use App\Models\Cidade;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Visita;
use App\Services\Visita\Agenda\Liberacao\Service as LiberacaoAgendaService;
use Illuminate\Support\Facades\Auth;

class Service
{
    public function __construct(private LiberacaoAgendaService $liberacaoAgendaService) {}

    public function buscarDados(?Visita $visita, ?int $cidadeId = null): array
    {
        $hospitais = Hospital::query()
            ->where('ativo', true)
            ->with(['alas:id,hospital_id,nome'])
            ->orderBy('nome')
            ->get(['id', 'nome', 'cidade_id', 'ativo']);

        $cidades = Cidade::query()
            ->whereIn('nome', ['Santa Maria', 'Porto Alegre', 'Pelotas'])
            ->orderBy('nome')
            ->get(['id', 'nome']);

        if ($visita) {
            $visita->load([
                'hospital.alas:id,hospital_id,nome',
                'alaUnidade:id,nome,hospital_id',
                'participantes.voluntario:id,name',
            ]);

            if ($visita->hospital && ! $hospitais->contains('id', $visita->hospital_id)) {
                $hospitais->push($visita->hospital);
                $hospitais = $hospitais->sortBy('nome')->values();
            }
        }

        $lideres = User::query()
            ->where('status', User::STATUS_ATIVO)
            ->whereNotNull('voluntario_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        $idsExtras = collect([Auth::id(), $visita?->lider_id])->filter()->unique();
        foreach ($idsExtras as $id) {
            if (! $lideres->contains('id', $id)) {
                $user = User::query()->find($id, ['id', 'name']);
                if ($user) {
                    $lideres->push($user);
                }
            }
        }
        $lideres = $lideres->sortBy('name')->values();

        $retorno = [
            'hospitais'                   => $hospitais,
            'cidades'                     => $cidades,
            'lideres'                     => $lideres,
            'meses_liberados'             => $this->buscarMesesLiberados($cidadeId ?: $visita?->hospital?->cidade_id),
            'meses_liberados_por_cidade'  => $cidades
                ->mapWithKeys(fn (Cidade $cidade) => [
                    $cidade->id => $this->buscarMesesLiberados((int) $cidade->id),
                ])
                ->all(),
        ];

        if ($visita) {
            $visitaDados = $visita->toArray();
            $visitaDados['inicio_em'] = $visita->inicio_em?->format('Y-m-d H:i:s');
            $visitaDados['fim_em'] = $visita->fim_em?->format('Y-m-d H:i:s');
            $retorno['visita'] = $visitaDados;
        }

        return $retorno;
    }

    /**
     * @return array<int, string>
     */
    private function buscarMesesLiberados(?int $cidadeId = null): array
    {
        $userId = Auth::id();

        if (! $userId) {
            return [];
        }

        $user = User::query()->with('voluntario')->find($userId);

        if (! $user) {
            return [];
        }

        $cidadeConsultaId = $cidadeId ?: $user->voluntario?->cidade_base_id;

        if (! $cidadeConsultaId) {
            return [];
        }

        return $this->liberacaoAgendaService->listarMesesLiberados((int) $cidadeConsultaId);
    }
}
