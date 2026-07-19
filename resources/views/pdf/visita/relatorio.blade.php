<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório da visita #{{ $relatorio->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 32px;
        }
        h1 {
            font-size: 20px;
            margin: 0 0 4px;
            color: #78350f;
        }
        .subtitulo {
            color: #6b7280;
            margin-bottom: 24px;
        }
        h2 {
            font-size: 14px;
            margin: 24px 0 8px;
            color: #92400e;
            border-bottom: 1px solid #fde68a;
            padding-bottom: 4px;
        }
        dl {
            margin: 0;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
        }
        dt {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #9ca3af;
            margin-bottom: 2px;
        }
        dd {
            margin: 0 0 8px;
            color: #111827;
        }
        .campo {
            margin-bottom: 16px;
        }
        .campo-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #9ca3af;
            margin-bottom: 4px;
        }
        .campo-valor {
            white-space: pre-wrap;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-alerta {
            background: #ffedd5;
            color: #9a3412;
        }
    </style>
</head>
<body>
    <h1>Relatório de visita</h1>
    <p class="subtitulo">
        {{ $visita->hospital?->nome ?? 'Hospital' }}
        · Relatório #{{ $relatorio->id }}
    </p>

    @if ($relatorio->fora_do_prazo)
        <p><span class="badge badge-alerta">Enviado fora do prazo recomendado</span></p>
    @endif

    <h2>Contexto da visita</h2>
    <dl class="grid">
        <div>
            <dt>Hospital</dt>
            <dd>{{ $visita->hospital?->nome ?? '—' }}</dd>
        </div>
        <div>
            <dt>Ala / Unidade</dt>
            <dd>{{ $visita->alaUnidade?->nome ?? '—' }}</dd>
        </div>
        <div>
            <dt>Início</dt>
            <dd>{{ $visita->inicio_em?->format('d/m/Y H:i') }}</dd>
        </div>
        <div>
            <dt>Fim</dt>
            <dd>{{ $visita->fim_em?->format('d/m/Y H:i') }}</dd>
        </div>
        <div>
            <dt>Líder</dt>
            <dd>{{ $visita->lider?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt>Status da visita</dt>
            <dd>{{ $visita->status->value }}</dd>
        </div>
    </dl>

    <div class="campo">
        <div class="campo-label">Participantes</div>
        <div class="campo-valor">
            @php
                $participantes = $visita->participantes
                    ?->filter(fn ($p) => $p->papel_na_visita->value === 'participante')
                    ?? collect();
                $palhacos = $participantes->filter(fn ($p) => $p->tipo_participacao->value === 'palhaco')->count();
                $paisanas = $participantes->filter(fn ($p) => $p->tipo_participacao->value === 'paisana')->count();
            @endphp
            @if ($palhacos > 0)
                Palhaço(s): {{ $palhacos }}
            @endif
            @if ($paisanas > 0)
                @if ($palhacos > 0) · @endif
                Paisana(s): {{ $paisanas }}
            @endif
            @if ($palhacos === 0 && $paisanas === 0)
                —
            @endif
        </div>
    </div>

    <h2>Relatório</h2>
    <dl class="grid">
        <div>
            <dt>Tipo</dt>
            <dd>{{ $relatorio->tipo_relatorio->value }}</dd>
        </div>
        <div>
            <dt>Autor</dt>
            <dd>{{ $relatorio->autor?->name ?? '—' }}</dd>
        </div>
        <div>
            <dt>Enviado em</dt>
            <dd>{{ $relatorio->enviado_em?->format('d/m/Y H:i') }}</dd>
        </div>
    </dl>

    <div class="campo">
        <div class="campo-label">Resumo</div>
        <div class="campo-valor">{{ $relatorio->resumo }}</div>
    </div>

    @if ($relatorio->feedback)
        <div class="campo">
            <div class="campo-label">Feedback</div>
            <div class="campo-valor">{{ $relatorio->feedback }}</div>
        </div>
    @endif

    @if ($relatorio->quartos_visitados !== null)
        <div class="campo">
            <div class="campo-label">Quartos visitados</div>
            <div class="campo-valor">{{ $relatorio->quartos_visitados }}</div>
        </div>
    @endif

    @if ($relatorio->pessoas_impactadas !== null)
        <div class="campo">
            <div class="campo-label">Pessoas impactadas</div>
            <div class="campo-valor">{{ $relatorio->pessoas_impactadas }}</div>
        </div>
    @endif

    @if ($relatorio->observacao_visitantes_externos)
        <div class="campo">
            <div class="campo-label">Observação sobre visitantes externos</div>
            <div class="campo-valor">{{ $relatorio->observacao_visitantes_externos }}</div>
        </div>
    @endif

    @if ($relatorio->observacoes_gerais)
        <div class="campo">
            <div class="campo-label">Observações gerais</div>
            <div class="campo-valor">{{ $relatorio->observacoes_gerais }}</div>
        </div>
    @endif
</body>
</html>
