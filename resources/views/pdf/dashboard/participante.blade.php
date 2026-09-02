<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Participação dos voluntários</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 28px 32px;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 2px;
            color: #78350f;
        }
        .subtitulo {
            color: #6b7280;
            margin: 0 0 4px;
            font-size: 11px;
        }
        .filtros {
            font-size: 10px;
            color: #9ca3af;
            margin-bottom: 18px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead tr {
            background: #fef3c7;
        }
        th {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #92400e;
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #fde68a;
            white-space: nowrap;
        }
        td {
            padding: 5px 8px;
            border-bottom: 1px solid #fef3c7;
            vertical-align: top;
        }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: #fffbeb; }
        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: 600;
        }
        .dentro_meta      { background: #d1fae5; color: #065f46; }
        .atencao          { background: #fef3c7; color: #92400e; }
        .compensacao_pendente { background: #fef3c7; color: #92400e; }
        .requer_analise   { background: #fee2e2; color: #991b1b; }
        .isento           { background: #f3f4f6; color: #374151; }
        .dados_insuficientes { background: #f3f4f6; color: #374151; }
        .num { text-align: right; }
        .rodape {
            margin-top: 16px;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
        }
    </style>
</head>
<body>

<h1>Participação dos voluntários</h1>
<p class="subtitulo">Dashboard de acompanhamento — Esquadrão da Alegria</p>
<p class="filtros">
    Gerado em {{ $geradoEm }}
    @if(!empty($filtros['cidade_id'])) · Cidade ID: {{ $filtros['cidade_id'] }} @endif
    · Período: {{ $filtros['periodo_tipo'] === 'mes' ? 'Mês ' . ($filtros['mes'] ?? '') . '/' . $filtros['ano'] : ($filtros['periodo_tipo'] === 'semestre' ? ($filtros['semestre'] ?? '') . 'º semestre/' . $filtros['ano'] : 'Ano ' . $filtros['ano']) }}
    · {{ $participantes->count() }} voluntários
</p>

<table>
    <thead>
        <tr>
            <th>Voluntário</th>
            <th>Cidade</th>
            <th>Tipo</th>
            <th class="num">Visitas</th>
            <th class="num">Saldo</th>
            <th>Situação</th>
            <th class="num">Reuniões</th>
            <th class="num">Oficinas</th>
            <th class="num">Rel. pend.</th>
            <th>Última ativ.</th>
        </tr>
    </thead>
    <tbody>
        @forelse($participantes as $p)
        <tr>
            <td>
                <strong>{{ $p['nome'] }}</strong>
                @if($p['cargos']->isNotEmpty())
                <br><span style="font-size:9px;color:#6b7280">{{ $p['cargos']->implode(', ') }}</span>
                @endif
            </td>
            <td>{{ $p['cidade'] }}</td>
            <td>{{ match($p['tipo_atuacao']) { 'visitas' => 'Visitas', 'isento' => 'Isento', default => '—' } }}</td>
            <td class="num">{{ $p['visitas_validas'] }}</td>
            <td class="num">{{ $p['saldo_atual'] ?? '—' }}</td>
            <td>
                <span class="badge {{ $p['situacao'] }}">
                    {{ match($p['situacao']) {
                        'dentro_meta'          => 'Dentro da meta',
                        'atencao'              => 'Atenção',
                        'compensacao_pendente' => 'Comp. pendente',
                        'requer_analise'       => 'Requer análise',
                        'isento'               => 'Isento',
                        default                => 'Dados insuf.',
                    } }}
                </span>
            </td>
            <td class="num">{{ $p['reunioes']['percentual'] !== null ? $p['reunioes']['percentual'] . '%' : '—' }}</td>
            <td class="num">{{ $p['oficinas']['percentual'] !== null ? $p['oficinas']['percentual'] . '%' : '—' }}</td>
            <td class="num">{{ $p['relatorios_pendentes'] }}</td>
            <td>{{ $p['ultima_atividade'] ? \Carbon\Carbon::parse($p['ultima_atividade'])->format('d/m/Y') : '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" style="text-align:center;padding:20px;color:#9ca3af">
                Nenhum voluntário encontrado para os filtros selecionados.
            </td>
        </tr>
        @endforelse
    </tbody>
</table>

<p class="rodape">
    Os indicadores organizam os dados para análise humana e não representam decisões automáticas.
    A coordenação deve considerar contexto, justificativas e histórico antes de qualquer decisão.
</p>

</body>
</html>
