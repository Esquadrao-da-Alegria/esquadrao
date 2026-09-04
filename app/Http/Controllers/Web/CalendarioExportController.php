<?php

namespace App\Http\Controllers\Web;

use App\Enums\VisitaStatus;
use App\Http\Controllers\Controller;
use App\Models\Evento;
use App\Models\Visita;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CalendarioExportController extends Controller
{
    public function visitas(Request $request): Response
    {
        $user = Auth::user();
        $tipo = $request->query('tipo', 'minhas');

        $query = Visita::query()
            ->where('status', '!=', VisitaStatus::Cancelada->value)
            ->with(['hospital.cidade', 'alaUnidade', 'lider'])
            ->orderBy('inicio_em');

        if ($tipo === 'cidade') {
            $user->loadMissing('voluntario');
            $cidadeId = $user->voluntario?->cidade_base_id;

            if (! $cidadeId) {
                return $this->respostaIcs(
                    $this->gerarIcs('Visitas - Minha Cidade', collect(), fn ($v) => $this->visitaParaVevent($v)),
                    'visitas.ics',
                );
            }

            $query->where(function ($q) use ($cidadeId) {
                $q->whereHas('hospital', fn ($h) => $h->where('cidade_id', $cidadeId))
                    ->orWhere(fn ($h) => $h->whereNull('hospital_id')
                        ->whereHas('lider.voluntario', fn ($v) => $v->where('cidade_base_id', $cidadeId)));
            });
        } else {
            $query->whereHas('participantes', fn ($q) => $q->where('voluntario_id', $user->id));
        }

        $visitas        = $query->get();
        $nomeCalendario = $tipo === 'cidade' ? 'Visitas - Minha Cidade' : 'Minhas Visitas';

        return $this->respostaIcs(
            $this->gerarIcs($nomeCalendario, $visitas, fn ($v) => $this->visitaParaVevent($v)),
            'visitas.ics',
        );
    }

    public function eventos(Request $request): Response
    {
        $user = Auth::user();
        $user->loadMissing('voluntario');
        $cidadeId = $user->voluntario?->cidade_base_id;

        $eventos = Evento::query()
            ->where('status', '!=', 'cancelado')
            ->when($cidadeId, fn ($q) => $q->where(
                fn ($sub) => $sub->where('cidade_id', $cidadeId)->orWhereNull('cidade_id')
            ))
            ->with(['cidade'])
            ->orderBy('data_inicio')
            ->get();

        return $this->respostaIcs(
            $this->gerarIcs('Eventos - Esquadrão da Alegria', $eventos, fn ($e) => $this->eventoParaVevent($e)),
            'eventos.ics',
        );
    }

    private function respostaIcs(string $conteudo, string $arquivo): Response
    {
        return response($conteudo, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$arquivo}\"",
        ]);
    }

    private function gerarIcs(string $nome, Collection $itens, callable $conversor): string
    {
        $linhas = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Esquadrao da Alegria//Calendario//PT',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $this->escaparIcs($nome),
        ];

        foreach ($itens as $item) {
            foreach ($conversor($item) as $linha) {
                $linhas[] = $linha;
            }
        }

        $linhas[] = 'END:VCALENDAR';

        return implode("\r\n", $linhas) . "\r\n";
    }

    private function visitaParaVevent(Visita $visita): array
    {
        $inicio = Carbon::parse($visita->inicio_em)->utc();
        $fim    = $visita->fim_em
            ? Carbon::parse($visita->fim_em)->utc()
            : $inicio->copy()->addHours(2);

        $hospital = $visita->hospital;
        $titulo   = $hospital ? "Visita - {$hospital->nome}" : 'Visita Externa';
        $cidade   = $hospital?->cidade?->nome ?? '';
        $local    = $hospital
            ? ($cidade ? "{$hospital->nome}, {$cidade}" : $hospital->nome)
            : '';

        $descricao = [];
        if ($visita->alaUnidade) {
            $descricao[] = 'Ala/Unidade: ' . $visita->alaUnidade->nome;
        }
        if ($visita->lider) {
            $descricao[] = 'Líder: ' . $visita->lider->name;
        }

        return [
            'BEGIN:VEVENT',
            'UID:visita-' . $visita->id . '@esquadrao-da-alegria',
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $inicio->format('Ymd\THis\Z'),
            'DTEND:' . $fim->format('Ymd\THis\Z'),
            'SUMMARY:' . $this->escaparIcs($titulo),
            'LOCATION:' . $this->escaparIcs($local),
            'DESCRIPTION:' . $this->escaparIcs(implode("\n", $descricao)),
            'END:VEVENT',
        ];
    }

    private function eventoParaVevent(Evento $evento): array
    {
        $inicio = Carbon::parse($evento->data_inicio)->utc();
        $fim    = $evento->data_fim
            ? Carbon::parse($evento->data_fim)->utc()
            : $inicio->copy()->addHours(2);

        $tipoLabel = match ($evento->tipo) {
            'oficina' => 'Oficina',
            'reuniao' => 'Reunião',
            default   => 'Evento',
        };

        $titulo = $tipoLabel . ' - ' . $evento->titulo;
        $local  = $evento->local ?? '';
        if ($evento->cidade) {
            $local = $local ? "{$local}, {$evento->cidade->nome}" : $evento->cidade->nome;
        }

        return [
            'BEGIN:VEVENT',
            'UID:evento-' . $evento->id . '@esquadrao-da-alegria',
            'DTSTAMP:' . now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:' . $inicio->format('Ymd\THis\Z'),
            'DTEND:' . $fim->format('Ymd\THis\Z'),
            'SUMMARY:' . $this->escaparIcs($titulo),
            'LOCATION:' . $this->escaparIcs($local),
            'DESCRIPTION:' . $this->escaparIcs($evento->descricao ?? ''),
            'END:VEVENT',
        ];
    }

    private function escaparIcs(string $valor): string
    {
        $valor = str_replace('\\', '\\\\', $valor);
        $valor = str_replace(';', '\;', $valor);
        $valor = str_replace(',', '\,', $valor);
        $valor = str_replace(["\r\n", "\r", "\n"], '\n', $valor);

        return $valor;
    }
}
