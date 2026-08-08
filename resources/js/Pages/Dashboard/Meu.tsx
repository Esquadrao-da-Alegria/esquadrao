import HistoricoModal from '@/components/Painel/Dashboard/Meu/Historico/Modal/Show';
import PainelLayout from '@/layouts/PainelLayout';
import { meu } from '@/routes/dashboards';
import { show as eventoShow } from '@/routes/eventos';
import { edit as visitaEdit } from '@/routes/visitas';
import type {
    FiltrosMeuDashboard,
    HistoricoMeuDashboard,
    MedidaMeuDashboard,
    PaginacaoMeuDashboard,
} from '@/types/meu-dashboard';
import { Head, Link, router } from '@inertiajs/react';
import {
    Activity,
    ArrowRight,
    Building2,
    CalendarCheck,
    ChevronDown,
    CircleAlert,
    Eye,
    FileWarning,
    HeartHandshake,
    MapPin,
    UsersRound,
} from 'lucide-react';
import { useState } from 'react';

interface Props {
    voluntario: { nome: string; cidade: string; cargos: string[]; tipo_atuacao: string; possui_vinculo: boolean };
    indicadores: {
        visitas_validas: number; visitas_realizadas: number; meta_mensal: number | null; saldo: number | null; compensacao: string | null;
        reunioes: MedidaMeuDashboard; oficinas: MedidaMeuDashboard; impacto_estimado: number; hospitais_visitados: number;
        ultima_visita_valida: string | null; relatorios_pendentes: number; relatorios_fora_prazo: number;
    };
    evolucao: Array<{ mes: string; rotulo: string; validas: number; nao_contabilizadas: number; meta: number | null }>;
    compensacoes: Array<{ mes: string; meta: number; visitas: number; saldo: number; credito_anterior_utilizado: number; debito_anterior_compensado: number; credito_transferido: number; debito_transferido: number; situacao: string }>;
    presencas: Array<{ id: number; tipo: string; titulo: string; local: string | null; data: string; presenca: string | null; considerado: boolean; motivo: string }>;
    hospitais: Array<{ nome: string; total: number }>;
    companheiros: Array<{ id: number; name: string; visitas_compartilhadas: number }>;
    cidades: Array<{ nome: string; total: number }>;
    historico: PaginacaoMeuDashboard<HistoricoMeuDashboard>;
    proximas_atividades: Array<{ id: number; tipo: string; titulo: string; local: string; cidade: string; data: string }>;
    orientacoes: string[];
    filtros: FiltrosMeuDashboard;
    opcoes: { cidades: Array<{ id: number; nome: string }> };
}

const formatarData = (valor: string) => new Date(valor).toLocaleDateString('pt-BR', { day: '2-digit', month: 'short', year: 'numeric' });

export default function MeuDashboard(props: Props) {
    const { voluntario, indicadores, evolucao, compensacoes, presencas, hospitais, companheiros, cidades, historico, proximas_atividades, orientacoes, filtros, opcoes } = props;
    const [maisFiltros, setMaisFiltros] = useState(filtros.periodo_tipo === 'personalizado');
    const [dataInicio, setDataInicio] = useState(filtros.data_inicio ?? '');
    const [dataFim, setDataFim] = useState(filtros.data_fim ?? '');
    const [itemSelecionado, setItemSelecionado] = useState<HistoricoMeuDashboard | null>(null);

    const consultar = (alteracoes: Record<string, string | number | null | undefined>) => {
        const query = { ...filtros, ...alteracoes, page: undefined };
        router.get(meu().url, Object.fromEntries(Object.entries(query).filter(([, valor]) => valor !== '' && valor !== null && valor !== undefined)), { preserveState: true, preserveScroll: true, replace: true });
    };

    const periodoInvalido = Boolean(dataInicio && dataFim && (new Date(dataFim) < new Date(dataInicio) || new Date(dataFim) > new Date(new Date(dataInicio).setMonth(new Date(dataInicio).getMonth() + 24))));
    const maiorEvolucao = Math.max(...evolucao.flatMap((item) => [item.validas, item.nao_contabilizadas, item.meta ?? 0]), 1);

    return (
        <PainelLayout>
            <Head title="Meu dashboard" />
            <div className="mx-auto max-w-7xl space-y-6 px-5 py-8 sm:px-6 lg:px-8">
                <header>
                    <p className="text-sm font-semibold text-amber-700">Minha participação</p>
                    <h1 className="mt-1 text-2xl font-semibold text-amber-950 sm:text-3xl">Olá, {voluntario.nome.split(' ')[0]}</h1>
                    <p className="mt-2 max-w-3xl text-sm leading-relaxed text-amber-900/60">Acompanhe suas atividades, entenda os cálculos e organize os próximos passos. Este painel informa e não altera seu cadastro.</p>
                </header>

                {!voluntario.possui_vinculo && (
                    <section className="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                        <h2 className="font-semibold text-amber-950">Conta administrativa sem voluntário vinculado</h2>
                        <p className="mt-1 text-sm leading-relaxed text-amber-900/70">Você pode acessar e testar esta página, mas os indicadores pessoais permanecerão vazios até que exista um voluntário vinculado à conta. Nenhum dado de outra pessoa será usado para preencher o dashboard.</p>
                    </section>
                )}

                <section className="space-y-3">
                    <div className="flex flex-col gap-3 lg:flex-row">
                        <select value={filtros.periodo_tipo} onChange={(event) => { const tipo = event.target.value; setMaisFiltros(tipo === 'personalizado'); consultar({ periodo_tipo: tipo, mes: tipo === 'mes' ? filtros.mes ?? new Date().getMonth() + 1 : undefined, semestre: tipo === 'semestre' ? filtros.semestre ?? 1 : undefined }); }} className="h-11 rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none lg:w-48">
                            <option value="mes">Este mês</option><option value="semestre">Por semestre</option><option value="ano">Por ano</option><option value="personalizado">Período personalizado</option>
                        </select>
                        <select value={filtros.cidade_id ?? ''} onChange={(event) => consultar({ cidade_id: event.target.value || undefined })} className="h-11 rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none lg:w-60">
                            <option value="">Todas as cidades</option>{opcoes.cidades.map((cidade) => <option key={cidade.id} value={cidade.id}>{cidade.nome}</option>)}
                        </select>
                        <select value={filtros.atividade ?? ''} onChange={(event) => consultar({ atividade: event.target.value || undefined })} className="h-11 rounded-2xl border border-gray-200 bg-white px-4 text-sm shadow-sm focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none lg:w-52">
                            <option value="">Todas as atividades</option><option value="visitas">Visitas</option><option value="reunioes">Reuniões</option><option value="oficinas">Oficinas</option>
                        </select>
                        <button type="button" onClick={() => setMaisFiltros((aberto) => !aberto)} aria-expanded={maisFiltros} className="inline-flex h-11 items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-4 text-sm font-medium text-amber-900 shadow-sm hover:border-amber-300 hover:bg-amber-50">Ajustar período <ChevronDown className={`size-4 transition ${maisFiltros ? 'rotate-180' : ''}`} /></button>
                    </div>
                    {maisFiltros && <div className="grid gap-3 rounded-2xl border border-amber-100 bg-amber-50/40 p-4 sm:grid-cols-2 lg:grid-cols-4">
                        {filtros.periodo_tipo !== 'personalizado' && <Campo label="Ano"><input type="number" min="2020" max="2100" value={filtros.ano ?? new Date().getFullYear()} onChange={(event) => consultar({ ano: event.target.value })} /></Campo>}
                        {filtros.periodo_tipo === 'mes' && <Campo label="Mês"><select value={filtros.mes ?? ''} onChange={(event) => consultar({ mes: event.target.value })}>{Array.from({ length: 12 }, (_, indice) => <option key={indice + 1} value={indice + 1}>{new Date(2026, indice).toLocaleDateString('pt-BR', { month: 'long' })}</option>)}</select></Campo>}
                        {filtros.periodo_tipo === 'semestre' && <Campo label="Semestre"><select value={filtros.semestre ?? 1} onChange={(event) => consultar({ semestre: event.target.value })}><option value="1">1º semestre</option><option value="2">2º semestre</option></select></Campo>}
                        {filtros.periodo_tipo === 'personalizado' && <><Campo label="Data inicial"><input type="date" value={dataInicio} onChange={(event) => setDataInicio(event.target.value)} /></Campo><Campo label="Data final"><input type="date" value={dataFim} onChange={(event) => setDataFim(event.target.value)} /></Campo><div className="self-end lg:col-span-2"><button type="button" disabled={!dataInicio || !dataFim || periodoInvalido} onClick={() => consultar({ data_inicio: dataInicio, data_fim: dataFim })} className="rounded-full bg-amber-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-50">Consultar período</button><p className={`mt-1 text-xs ${periodoInvalido ? 'text-red-600' : 'text-gray-500'}`}>{periodoInvalido ? 'Use uma data final posterior à inicial e um intervalo de até 24 meses.' : 'O intervalo pode ter no máximo 24 meses.'}</p></div></>}
                    </div>}
                </section>

                <section className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <Indicador icon={CalendarCheck} titulo="Visitas válidas" valor={indicadores.visitas_validas} detalhe={`${indicadores.visitas_realizadas} realizadas no período`} />
                    <Indicador icon={Activity} titulo="Eventos documentados" valor={indicadores.visitas_validas + indicadores.reunioes.presencas + indicadores.oficinas.presencas} detalhe="Visitas, reuniões e oficinas" />
                    <Indicador icon={HeartHandshake} titulo={indicadores.meta_mensal === null ? 'Meta aplicável' : 'Saldo atual'} valor={indicadores.meta_mensal === null ? 'Não se aplica' : `${indicadores.saldo !== null && indicadores.saldo > 0 ? '+' : ''}${indicadores.saldo ?? 0}`} detalhe={indicadores.meta_mensal === null ? 'Confira a orientação abaixo' : `Meta de ${indicadores.meta_mensal} visitas/mês`} />
                    <Indicador icon={FileWarning} titulo="Relatórios pendentes" valor={indicadores.relatorios_pendentes} detalhe={`${indicadores.relatorios_fora_prazo} enviados fora do prazo`} />
                </section>

                <section className="grid gap-3 sm:grid-cols-3" aria-label="Outros números do período">
                    <MiniDado titulo="Impacto estimado" valor={`${indicadores.impacto_estimado} pessoas`} />
                    <MiniDado titulo="Hospitais diferentes" valor={String(indicadores.hospitais_visitados)} />
                    <MiniDado titulo="Última visita válida" valor={indicadores.ultima_visita_valida ? formatarData(indicadores.ultima_visita_valida) : 'Ainda não registrada'} />
                </section>

                <section className="rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50 to-yellow-50/50 p-5"><h2 className="font-semibold text-amber-950">Orientações para você</h2><div className="mt-3 grid gap-2 md:grid-cols-2">{orientacoes.map((orientacao) => <p key={orientacao} className="flex gap-2 rounded-xl bg-white/80 px-4 py-3 text-sm leading-relaxed text-amber-900/70"><CircleAlert className="mt-0.5 size-4 shrink-0 text-amber-600" />{orientacao}</p>)}</div></section>

                <section className="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
                    <Bloco titulo="Visitas por mês" descricao="Visitas válidas e realizadas que ainda não entraram na contabilização.">
                        <div className="flex min-h-52 items-end gap-3 overflow-x-auto pt-5">{evolucao.map((item) => <div key={item.mes} className="flex min-w-16 flex-1 flex-col items-center"><div className="flex h-36 items-end gap-1"><Barra valor={item.validas} maximo={maiorEvolucao} classe="bg-amber-600" rotulo={`${item.validas} válidas`} /><Barra valor={item.nao_contabilizadas} maximo={maiorEvolucao} classe="bg-gray-300" rotulo={`${item.nao_contabilizadas} não contabilizadas`} /></div><span className="mt-2 text-xs capitalize text-gray-500">{item.rotulo}</span>{item.meta !== null && <span className="text-[10px] text-amber-700">meta {item.meta}</span>}</div>)}</div>
                        <div className="mt-4 flex gap-4 text-xs text-gray-500"><span><i className="mr-1 inline-block size-2 rounded-full bg-amber-600" />Válidas</span><span><i className="mr-1 inline-block size-2 rounded-full bg-gray-300" />Não contabilizadas</span></div>
                    </Bloco>
                    <Bloco titulo="Presença no período" descricao="Cálculo baseado nos eventos finalizados da sua cidade-base."><Presenca titulo="Reuniões" medida={indicadores.reunioes} /><Presenca titulo="Oficinas" medida={indicadores.oficinas} /><button type="button" onClick={() => document.getElementById('atividades-consideradas')?.scrollIntoView({ behavior: 'smooth' })} className="mt-4 text-sm font-semibold text-amber-700 hover:underline">Ver atividades consideradas</button></Bloco>
                </section>

                {compensacoes.length > 0 && <Bloco titulo="Saldo e compensação" descricao="Créditos e débitos valem somente para o mês imediatamente seguinte."><div className="overflow-x-auto"><table className="w-full min-w-[720px] text-left text-sm"><thead className="text-xs uppercase text-amber-900/60"><tr><th className="py-2">Mês</th><th>Meta</th><th>Visitas</th><th>Saldo</th><th>Crédito usado</th><th>Débito compensado</th><th>Situação</th></tr></thead><tbody className="divide-y divide-amber-50">{compensacoes.map((item) => <tr key={item.mes}><td className="py-3 font-medium">{item.mes}</td><td>{item.meta}</td><td>{item.visitas}</td><td>{item.saldo > 0 ? `+${item.saldo}` : item.saldo}</td><td>{item.credito_anterior_utilizado}</td><td>{item.debito_anterior_compensado}</td><td className="capitalize">{item.situacao.replaceAll('_', ' ')}</td></tr>)}</tbody></table></div></Bloco>}

                <section className="grid gap-4 md:grid-cols-3"><Ranking titulo="Hospitais visitados" icon={Building2} itens={hospitais} vazio="Nenhum hospital no período." /><Ranking titulo="Atuação por cidade" icon={MapPin} itens={cidades} vazio="Nenhuma cidade no período." /><Ranking titulo="Companheiros frequentes" icon={UsersRound} itens={companheiros.map((item) => ({ nome: item.name, total: item.visitas_compartilhadas }))} vazio="Nenhuma visita compartilhada no período." /></section>

                <Bloco titulo="Próximas atividades" descricao="Somente visitas confirmadas e inscrições ativas.">{proximas_atividades.length === 0 ? <Vazio texto="Você não possui atividades futuras confirmadas." /> : <div className="grid gap-3 md:grid-cols-2">{proximas_atividades.map((item) => <article key={`${item.tipo}-${item.id}`} className="flex items-center justify-between gap-3 rounded-xl border border-amber-100 p-4"><div><span className="text-xs font-semibold uppercase text-amber-700">{item.tipo}</span><h3 className="font-semibold text-amber-950">{item.titulo}</h3><p className="text-xs text-gray-500">{formatarData(item.data)} · {item.cidade}</p></div><Link href={item.tipo === 'visita' ? visitaEdit(item.id) : eventoShow(item.id)} aria-label={`Ver ${item.titulo}`} className="flex size-9 shrink-0 items-center justify-center rounded-full border border-amber-200 text-amber-700 hover:bg-amber-50"><ArrowRight className="size-4" /></Link></article>)}</div>}</Bloco>

                <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm"><div className="border-b border-amber-100 p-5"><h2 className="font-semibold text-amber-950">Histórico pessoal</h2><p className="mt-1 text-sm text-gray-500">Abra os detalhes para entender como cada atividade foi considerada.</p></div><div className="divide-y divide-amber-50">{historico.data.map((item) => <article key={`${item.tipo}-${item.id}`} className="flex items-center justify-between gap-3 p-4 transition hover:bg-amber-50/40 sm:px-5"><div className="min-w-0"><div className="flex items-center gap-2"><span className="text-xs font-semibold uppercase text-amber-700">{item.tipo}</span><span className={`rounded-full px-2 py-0.5 text-[11px] font-medium ${item.situacao === 'contabilizada' || item.situacao === 'presente' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'}`}>{item.situacao.replaceAll('_', ' ')}</span></div><h3 className="mt-1 truncate font-semibold text-amber-950">{item.local}</h3><p className="truncate text-xs text-gray-500">{formatarData(item.data)} · {item.cidade}</p></div><button type="button" onClick={() => setItemSelecionado(item)} aria-label={`Ver detalhes de ${item.local}`} className="flex size-9 shrink-0 items-center justify-center rounded-full border border-amber-200 text-amber-700 hover:bg-amber-50"><Eye className="size-4" /></button></article>)}</div>{historico.data.length === 0 && <Vazio texto="Nenhuma atividade encontrada para os filtros selecionados." />}<div className="flex items-center justify-between border-t border-amber-100 p-4 text-sm text-gray-500"><span>Página {historico.current_page} de {historico.last_page}</span><div className="flex gap-2">{historico.prev_page_url && <Link preserveScroll href={historico.prev_page_url} className="rounded-full border border-amber-200 px-3 py-1.5 text-amber-800">Anterior</Link>}{historico.next_page_url && <Link preserveScroll href={historico.next_page_url} className="rounded-full border border-amber-200 px-3 py-1.5 text-amber-800">Próxima</Link>}</div></div></section>

                <Bloco titulo="Atividades consideradas" descricao="Detalhamento transparente das reuniões e oficinas usadas no percentual."><div id="atividades-consideradas" className="grid gap-3 md:grid-cols-2">{presencas.map((item) => <article key={item.id} className="rounded-xl border border-gray-100 p-3"><div className="flex justify-between gap-3"><div><p className="text-xs font-semibold uppercase text-amber-700">{item.tipo}</p><h3 className="text-sm font-semibold text-gray-900">{item.titulo}</h3></div><span className={`h-fit rounded-full px-2 py-1 text-[11px] ${item.considerado ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600'}`}>{item.considerado ? 'Considerada' : 'Aguardando dados'}</span></div><p className="mt-2 text-xs text-gray-500">{formatarData(item.data)} · {item.motivo}</p></article>)}</div>{presencas.length === 0 && <Vazio texto="Não houve reuniões ou oficinas finalizadas na sua cidade-base durante o período." />}</Bloco>
            </div>
            <HistoricoModal item={itemSelecionado} onOpenChange={(aberto) => !aberto && setItemSelecionado(null)} />
        </PainelLayout>
    );
}

function Campo({ label, children }: { label: string; children: React.ReactNode }) { return <label className="space-y-1.5 text-sm font-medium text-amber-950"><span>{label}</span><div className="[&_input]:h-10 [&_input]:w-full [&_input]:rounded-xl [&_input]:border [&_input]:border-gray-200 [&_input]:bg-white [&_input]:px-3 [&_select]:h-10 [&_select]:w-full [&_select]:rounded-xl [&_select]:border [&_select]:border-gray-200 [&_select]:bg-white [&_select]:px-3">{children}</div></label>; }
function Indicador({ icon: Icone, titulo, valor, detalhe }: { icon: typeof Activity; titulo: string; valor: number | string; detalhe: string }) { return <article className="rounded-xl border border-amber-100 bg-white p-4 shadow-sm"><div className="flex items-center justify-between"><div><p className="text-xs font-medium text-amber-900/60">{titulo}</p><p className="mt-1 text-2xl font-bold text-amber-950">{valor}</p></div><span className="flex size-9 items-center justify-center rounded-full bg-amber-50"><Icone className="size-4 text-amber-600" /></span></div><p className="mt-2 text-xs text-gray-500">{detalhe}</p></article>; }
function MiniDado({ titulo, valor }: { titulo: string; valor: string }) { return <div className="flex items-center justify-between gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3"><span className="text-xs font-medium text-gray-500">{titulo}</span><strong className="text-right text-sm text-gray-800">{valor}</strong></div>; }
function Bloco({ titulo, descricao, children }: { titulo: string; descricao: string; children: React.ReactNode }) { return <section className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm"><h2 className="font-semibold text-amber-950">{titulo}</h2><p className="mt-1 text-sm text-gray-500">{descricao}</p><div className="mt-4">{children}</div></section>; }
function Barra({ valor, maximo, classe, rotulo }: { valor: number; maximo: number; classe: string; rotulo: string }) { return <div title={rotulo} aria-label={rotulo} className={`w-4 rounded-t ${classe}`} style={{ height: `${Math.max(valor ? 8 : 0, valor / maximo * 100)}%` }} />; }
function Presenca({ titulo, medida }: { titulo: string; medida: MedidaMeuDashboard }) { return <div className="mt-4"><div className="flex justify-between text-sm"><span className="font-medium text-amber-950">{titulo}</span><span>{medida.percentual === null ? 'Dados indisponíveis' : `${medida.percentual}%`}</span></div><div className="mt-2 h-2 overflow-hidden rounded-full bg-gray-100"><div className="h-full rounded-full bg-amber-500" style={{ width: `${medida.percentual ?? 0}%` }} /></div><p className="mt-1 text-xs text-gray-500">{medida.presencas} presenças em {medida.oferecidos} atividades</p></div>; }
function Ranking({ titulo, icon: Icone, itens, vazio }: { titulo: string; icon: typeof Building2; itens: Array<{ nome: string; total: number }>; vazio: string }) { return <section className="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm"><div className="flex items-center gap-2"><Icone className="size-4 text-amber-600" /><h2 className="font-semibold text-amber-950">{titulo}</h2></div><div className="mt-4 space-y-2">{itens.map((item) => <div key={item.nome} className="flex items-center justify-between rounded-xl bg-amber-50/60 px-3 py-2"><span className="truncate text-sm text-amber-950">{item.nome}</span><span className="ml-3 rounded-full bg-white px-2 py-0.5 text-xs font-bold text-amber-700">{item.total}</span></div>)}{itens.length === 0 && <p className="text-sm text-gray-500">{vazio}</p>}</div></section>; }
function Vazio({ texto }: { texto: string }) { return <p className="p-6 text-center text-sm text-gray-500">{texto}</p>; }
