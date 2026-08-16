import PainelLayout from '@/layouts/PainelLayout'
import { labelStatus, labelTipo } from '@/lib/evento'
import { type Evento, type EventoParticipante, type SharedData } from '@/types'
import { Link, router, usePage } from '@inertiajs/react'
import { ArrowLeft, Settings, Trash2, UserCheck, UserX, Users } from 'lucide-react'
import { useState } from 'react'
import AjusteParticipacao from '@/components/Painel/Evento/AjusteParticipacao'

interface Props { evento: Evento; inscrito: boolean; presenca_marcada: boolean; pode_gerenciar: boolean; ajustes_participacao: any[]; voluntarios_ajuste: Array<{ id: number; name: string }> }

const fmt = (v?: string | null) =>
  v ? new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(v)) : '-'

const badgeStatus: Record<string, string> = {
  agendado: 'border-amber-200/80 bg-white text-amber-800',
  cancelado: 'border-red-200/80 bg-red-50 text-red-700',
  finalizado: 'border-gray-200/80 bg-gray-50 text-gray-600',
}

const badgePresenca: Record<string, string> = {
  presente: 'bg-green-100 text-green-800',
  ausente: 'bg-red-100 text-red-800',
}

function vagasLabel(count: number, limite: number | null): string {
  if (limite === null) return `${count} inscrito${count !== 1 ? 's' : ''}`
  const livres = limite - count
  return `${count} de ${limite} inscritos · ${livres} ${livres === 1 ? 'vaga disponível' : 'vagas disponíveis'}`
}

function PresencaForm({ evento, participantes }: { evento: Evento; participantes: EventoParticipante[] }) {
  const [presencas, setPresencas] = useState<Record<number, { presenca: string; observacao_presenca: string }>>(
    Object.fromEntries(
      participantes.map((p) => [
        p.id,
        { presenca: p.pivot?.presenca ?? '', observacao_presenca: p.pivot?.observacao_presenca ?? '' },
      ]),
    ),
  )

  const salvar = () => {
    const payload = participantes.map((p) => ({
      user_id: p.id,
      presenca: presencas[p.id]?.presenca || null,
      observacao_presenca: presencas[p.id]?.observacao_presenca || null,
    }))
    router.put(`/eventos/${evento.id}/presencas`, { participantes: payload })
  }

  return (
    <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
      <div className="flex items-center gap-2 border-b border-amber-50 px-6 py-4">
        <Users className="size-4 text-amber-700/60" strokeWidth={1.75} aria-hidden />
        <h2 className="font-semibold text-amber-950">Registrar Presença</h2>
      </div>
      <div className="p-6 space-y-4">
        {participantes.length === 0 ? (
          <p className="text-sm text-amber-900/50">Nenhum participante inscrito.</p>
        ) : (
          <>
            <div className="space-y-3">
              {participantes.map((p) => (
                <div key={p.id} className="flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border border-amber-100 p-3">
                  <span className="flex-1 text-sm font-medium text-amber-950">
                    {p.name} <span className="text-amber-900/40 font-normal">({p.email})</span>
                  </span>
                  <select
                    className="cursor-pointer rounded-xl border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400"
                    value={presencas[p.id]?.presenca ?? ''}
                    onChange={(e) => setPresencas((prev) => ({ ...prev, [p.id]: { ...prev[p.id], presenca: e.target.value } }))}
                  >
                    <option value="">Não marcado</option>
                    <option value="presente">Presente</option>
                    <option value="ausente">Ausente</option>
                  </select>
                  <input
                    type="text"
                    className="rounded-xl border bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 sm:w-44"
                    placeholder="Observação"
                    value={presencas[p.id]?.observacao_presenca ?? ''}
                    onChange={(e) => setPresencas((prev) => ({ ...prev, [p.id]: { ...prev[p.id], observacao_presenca: e.target.value } }))}
                  />
                </div>
              ))}
            </div>
            <button
              className="cursor-pointer inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
              onClick={salvar}
            >
              Salvar presenças
            </button>
          </>
        )}
      </div>
    </section>
  )
}

function FinalizacaoForm({ evento }: { evento: Evento }) {
  const [observacoes, setObservacoes] = useState('')

  return (
    <section className="overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 shadow-sm">
      <div className="border-b border-amber-100 px-6 py-4">
        <h2 className="font-semibold text-amber-950">Finalizar Evento</h2>
        <p className="mt-0.5 text-xs text-amber-900/55">Após finalizar, o evento não poderá ser editado ou cancelado.</p>
      </div>
      <div className="p-6 space-y-4">
        <div>
          <label className="mb-2 block text-sm font-medium text-amber-900">Observações finais (opcional)</label>
          <textarea
            className="w-full rounded-xl border bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
            rows={3}
            value={observacoes}
            onChange={(e) => setObservacoes(e.target.value)}
          />
        </div>
        <button
          className="cursor-pointer inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-5 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
          onClick={() => router.post(`/eventos/${evento.id}/finalizar`, { observacoes_finalizacao: observacoes })}
        >
          Finalizar evento
        </button>
      </div>
    </section>
  )
}

export default function Show({ evento, inscrito, presenca_marcada, pode_gerenciar, ajustes_participacao, voluntarios_ajuste }: Props) {
  const { props } = usePage<SharedData>()
  const [motivo, setMotivo] = useState('')

  const participantes = evento.participantes_ativos ?? []
  const agora = new Date()
  const jaComecou = new Date(evento.data_inicio) < agora
  const limitePassou = evento.limite_inscricao ? new Date(evento.limite_inscricao) < agora : false
  const lotado = evento.limite_participantes !== null && (evento.participantes_ativos_count ?? 0) >= evento.limite_participantes
  const temParticipantes = (evento.participantes_ativos_count ?? 0) > 0
  const podeFinalizar = pode_gerenciar && evento.status === 'agendado' && jaComecou
  const podeRegistrarPresenca = pode_gerenciar && evento.status !== 'cancelado'
  const podeEditar = props.eh_administrador && evento.status === 'agendado'
  const podeCancelar = props.eh_administrador && evento.status === 'agendado'
  const podeInscrever = evento.status === 'agendado' && !jaComecou && !limitePassou && !lotado
  const podeCancelarInscricao = inscrito && evento.status === 'agendado' && !jaComecou && !presenca_marcada
  const podeExcluir = props.eh_administrador && !temParticipantes
  const podeAjustar = pode_gerenciar && evento.status !== 'cancelado' && new Date(evento.data_inicio) <= agora

  return (
    <PainelLayout>
      <div className="mx-auto max-w-4xl px-5 py-8 sm:px-6 lg:px-8 space-y-6">

        {/* Voltar */}
        <Link href="/eventos" className="cursor-pointer inline-flex items-center gap-1.5 text-sm font-medium text-amber-700 hover:underline">
          <ArrowLeft className="size-4" aria-hidden />
          Voltar para eventos
        </Link>

        {/* Cabeçalho */}
        <div className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
          <div className="p-6 sm:p-8 space-y-5">
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">{evento.titulo}</h1>
                <div className="mt-2 flex flex-wrap gap-2">
                  <span className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide ${badgeStatus[evento.status] ?? ''}`}>
                    {labelStatus(evento.status)}
                  </span>
                  <span className="inline-flex rounded-full border border-amber-100 bg-amber-50 px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide text-amber-700">
                    {labelTipo(evento.tipo)}
                  </span>
                </div>
              </div>

              {/* Ações do admin */}
              {(podeEditar || podeCancelar) && (
                <div className="flex items-center gap-2">
                  <span className="text-xs text-amber-900/40 font-medium flex items-center gap-1">
                    <Settings className="size-3.5" aria-hidden /> Admin
                  </span>
                  {podeEditar && (
                    <Link
                      href={`/eventos/${evento.id}/edit`}
                      className="cursor-pointer inline-flex items-center justify-center rounded-full border border-amber-200 px-4 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-50"
                    >
                      Editar
                    </Link>
                  )}
                </div>
              )}
            </div>

            <p className="text-sm text-amber-900/70">{evento.descricao ?? 'Sem descrição.'}</p>

            <dl className="grid gap-3 sm:grid-cols-2 text-sm">
              <div><dt className="font-medium text-amber-900">Cidade</dt><dd className="text-amber-900/70">{evento.cidade?.nome ?? '-'}</dd></div>
              <div><dt className="font-medium text-amber-900">Local</dt><dd className="text-amber-900/70">{evento.local ?? '-'}</dd></div>
              <div><dt className="font-medium text-amber-900">Início</dt><dd className="text-amber-900/70">{fmt(evento.data_inicio)}</dd></div>
              <div><dt className="font-medium text-amber-900">Fim</dt><dd className="text-amber-900/70">{fmt(evento.data_fim)}</dd></div>
              {evento.limite_inscricao && (
                <div><dt className="font-medium text-amber-900">Inscrições até</dt><dd className="text-amber-900/70">{fmt(evento.limite_inscricao)}</dd></div>
              )}
              <div><dt className="font-medium text-amber-900">Responsável</dt><dd className="text-amber-900/70">{evento.responsavel?.name ?? '-'}</dd></div>
              <div>
                <dt className="font-medium text-amber-900">Participantes</dt>
                <dd className="text-amber-900/70">{vagasLabel(evento.participantes_ativos_count ?? 0, evento.limite_participantes)}</dd>
              </div>
              {evento.status === 'finalizado' && (
                <div><dt className="font-medium text-amber-900">Finalizado em</dt><dd className="text-amber-900/70">{fmt(evento.finalizado_em)}</dd></div>
              )}
            </dl>

            {evento.status === 'finalizado' && evento.observacoes_finalizacao && (
              <div className="rounded-xl bg-gray-50 border border-gray-100 px-4 py-3 text-sm">
                <p className="font-medium text-gray-700">Observações de finalização</p>
                <p className="mt-1 text-gray-600">{evento.observacoes_finalizacao}</p>
              </div>
            )}

            {evento.status === 'cancelado' && evento.motivo_cancelamento && (
              <div className="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm">
                <p className="font-medium text-red-700">Motivo do cancelamento</p>
                <p className="mt-1 text-red-600">{evento.motivo_cancelamento}</p>
              </div>
            )}

            {/* Inscrição */}
            {(podeInscrever || podeCancelarInscricao || inscrito || (evento.status === 'agendado' && !inscrito)) && (
              <div className="border-t border-amber-50 pt-5 flex items-center gap-3 flex-wrap">
                {podeInscrever && !inscrito && (
                  <button
                    className="cursor-pointer inline-flex items-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50"
                    onClick={() => router.post(`/eventos/${evento.id}/inscricao`)}
                  >
                    <UserCheck className="size-4" aria-hidden />
                    Participar do evento
                  </button>
                )}
                {!inscrito && evento.status === 'agendado' && !podeInscrever && (
                  <span className="text-sm text-amber-900/60">
                    {lotado ? 'Vagas esgotadas.' : limitePassou ? 'Prazo de inscrições encerrado.' : 'Evento já iniciado.'}
                  </span>
                )}
                {podeCancelarInscricao && (
                  <button
                    className="cursor-pointer inline-flex items-center gap-2 rounded-full border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-600 transition hover:bg-gray-50"
                    onClick={() => router.delete(`/eventos/${evento.id}/inscricao`)}
                  >
                    <UserX className="size-4" aria-hidden />
                    Cancelar minha inscrição
                  </button>
                )}
                {inscrito && (
                  <span className="text-sm text-green-700 font-medium">
                    {presenca_marcada ? 'Você está inscrito · presença registrada' : 'Você está inscrito'}
                  </span>
                )}
              </div>
            )}

            {/* Cancelar evento (admin) */}
            {podeCancelar && (
              <div className="border-t border-amber-50 pt-5 space-y-3">
                <p className="text-sm font-medium text-amber-900 flex items-center gap-1.5">
                  <Settings className="size-4 text-amber-700/60" aria-hidden /> Cancelar evento
                </p>
                <textarea
                  className="w-full rounded-xl border bg-white px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"
                  rows={2}
                  placeholder="Motivo do cancelamento"
                  value={motivo}
                  onChange={(e) => setMotivo(e.target.value)}
                />
                <button
                  className="cursor-pointer inline-flex items-center gap-2 rounded-full border border-red-300 bg-white px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                  onClick={() => router.post(`/eventos/${evento.id}/cancelar`, { motivo_cancelamento: motivo })}
                >
                  Confirmar cancelamento
                </button>
              </div>
            )}
          </div>
        </div>

        {/* Finalizar evento */}
        {podeFinalizar && <FinalizacaoForm evento={evento} />}

        {/* Registrar presença */}
        {podeRegistrarPresenca && <PresencaForm evento={evento} participantes={participantes} />}

        {podeAjustar && <AjusteParticipacao eventoId={evento.id} voluntarios={voluntarios_ajuste} ajustes={ajustes_participacao} />}

        {/* Lista de participantes (somente leitura para usuários comuns) */}
        {!podeRegistrarPresenca && (
          <section className="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
            <div className="flex items-center gap-2 border-b border-amber-50 px-6 py-4">
              <Users className="size-4 text-amber-700/60" strokeWidth={1.75} aria-hidden />
              <h2 className="font-semibold text-amber-950">Participantes inscritos</h2>
            </div>
            <ul className="divide-y divide-amber-50">
              {participantes.length === 0 ? (
                <li className="px-6 py-4 text-sm text-amber-900/50">Nenhum participante inscrito.</li>
              ) : (
                participantes.map((p) => (
                  <li key={p.id} className="flex items-center justify-between px-6 py-3 text-sm">
                    <span className="text-amber-950 font-medium">{p.name} <span className="text-amber-900/40 font-normal">({p.email})</span></span>
                    {p.pivot?.presenca && (
                      <span className={`text-xs px-2.5 py-0.5 rounded-full font-medium ${badgePresenca[p.pivot.presenca] ?? ''}`}>
                        {p.pivot.presenca}
                      </span>
                    )}
                  </li>
                ))
              )}
            </ul>
          </section>
        )}

        {/* Zona de perigo (admin) */}
        {props.eh_administrador && (
          <section className="overflow-hidden rounded-2xl border border-red-100 bg-white shadow-sm">
            <div className="flex items-center gap-2 border-b border-red-50 px-6 py-4">
              <Trash2 className="size-4 text-red-500/70" strokeWidth={1.75} aria-hidden />
              <h2 className="font-semibold text-red-700">Zona de perigo</h2>
            </div>
            <div className="p-6">
              {podeExcluir ? (
                <>
                  <p className="mb-4 text-sm text-red-600/70">Esta ação é permanente e não pode ser desfeita.</p>
                  <button
                    className="cursor-pointer inline-flex items-center gap-2 rounded-full border border-red-300 bg-white px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50"
                    onClick={() => {
                      if (window.confirm('Tem certeza que deseja excluir este evento? Esta ação não pode ser desfeita.')) {
                        router.delete(`/eventos/${evento.id}`)
                      }
                    }}
                  >
                    <Trash2 className="size-4" aria-hidden />
                    Excluir evento
                  </button>
                </>
              ) : (
                <p className="text-sm text-red-600/70">
                  {evento.status === 'cancelado'
                    ? 'Este evento possui inscrições registradas e não pode ser excluído.'
                    : 'Este evento possui participantes e não pode ser excluído. Para encerrá-lo, use a opção de cancelar.'}
                </p>
              )}
            </div>
          </section>
        )}
      </div>
    </PainelLayout>
  )
}
