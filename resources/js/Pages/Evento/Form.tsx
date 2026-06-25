import { type Evento, type User } from '@/types'
import { Link, useForm } from '@inertiajs/react'
import { ArrowLeft, Check } from 'lucide-react'

interface Props { evento?: Evento; responsaveis: User[]; action: string; method: 'post' | 'put'; backHref: string }

const inputClass = 'w-full rounded-xl border bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400'
const labelClass = 'mb-2 block text-sm font-medium text-amber-900'

function splitDateTime(dt?: string | null) {
  if (!dt) return { date: '', time: '' }
  const [date, time] = dt.slice(0, 16).split('T')
  return { date: date ?? '', time: time ?? '' }
}

function joinDateTime(date: string, time: string): string {
  if (!date) return ''
  return `${date}T${time || '00:00'}`
}

export default function Form({ evento, responsaveis, action, method, backHref }: Props) {
  const inicioSplit = splitDateTime(evento?.data_inicio)
  const fimSplit = splitDateTime(evento?.data_fim)
  const limiteInscricaoSplit = splitDateTime(evento?.limite_inscricao)

  const form = useForm({
    titulo: evento?.titulo ?? '',
    tipo: (evento?.tipo ?? 'evento') as 'oficina' | 'reuniao' | 'evento',
    descricao: evento?.descricao ?? '',
    local: evento?.local ?? '',
    data_inicio_date: inicioSplit.date,
    data_inicio_time: inicioSplit.time,
    data_fim_date: fimSplit.date,
    data_fim_time: fimSplit.time,
    limite_inscricao_date: limiteInscricaoSplit.date,
    limite_inscricao_time: limiteInscricaoSplit.time,
    limite_participantes: evento?.limite_participantes?.toString() ?? '',
    responsavel_id: evento?.responsavel_id?.toString() ?? '',
    inscrever_me: false,
  })

  const submit = (e: React.FormEvent) => {
    e.preventDefault()
    form.transform((d) => ({
      titulo: d.titulo,
      tipo: d.tipo,
      descricao: d.descricao,
      local: d.local,
      data_inicio: joinDateTime(d.data_inicio_date, d.data_inicio_time),
      data_fim: joinDateTime(d.data_fim_date, d.data_fim_time || '23:59'),
      limite_inscricao: d.limite_inscricao_date ? joinDateTime(d.limite_inscricao_date, d.limite_inscricao_time) : null,
      limite_participantes: d.limite_participantes || null,
      responsavel_id: d.responsavel_id || null,
      inscrever_me: d.inscrever_me,
    }))

    if (method === 'post') form.post(action)
    else form.put(action)
  }

  const { data, setData, processing } = form
  const errors = form.errors as unknown as Record<string, string>

  return (
    <div className="overflow-hidden rounded-3xl border bg-white">
      <form id="evento-form" onSubmit={submit}>
        <div className="p-8 md:p-12 space-y-6">
          <h2 className="text-3xl font-bold text-amber-800 md:text-4xl">
            {evento ? 'Editar evento' : 'Novo evento'}
          </h2>

          {Object.keys(errors).length > 0 && (
            <div className="rounded-lg border border-amber-200 bg-amber-50 p-4 text-amber-800">
              <ul className="list-disc pl-4 space-y-1 text-sm">
                {Object.entries(errors).map(([k, v]) => <li key={k}>{v}</li>)}
              </ul>
            </div>
          )}

          <div>
            <label htmlFor="titulo" className={labelClass}>Título *</label>
            <input id="titulo" type="text" required placeholder="Ex: Oficina de Música" className={inputClass}
              value={data.titulo} onChange={(e) => setData('titulo', e.target.value)} />
          </div>

          <div className="grid gap-4 sm:grid-cols-2">
            <div>
              <label htmlFor="tipo" className={labelClass}>Tipo *</label>
              <select id="tipo" required className={`${inputClass} cursor-pointer`} value={data.tipo}
                onChange={(e) => setData('tipo', e.target.value as 'oficina' | 'reuniao' | 'evento')}>
                <option value="oficina">Oficina</option>
                <option value="reuniao">Reunião</option>
                <option value="evento">Evento</option>
              </select>
            </div>
            <div>
              <label htmlFor="responsavel_id" className={labelClass}>Responsável</label>
              <select id="responsavel_id" className={`${inputClass} cursor-pointer`} value={data.responsavel_id}
                onChange={(e) => setData('responsavel_id', e.target.value)}>
                <option value="">Selecione</option>
                {responsaveis.map((u) => <option key={u.id} value={u.id}>{u.name}</option>)}
              </select>
            </div>
          </div>

          <div>
            <label htmlFor="descricao" className={labelClass}>Descrição</label>
            <textarea id="descricao" rows={3} placeholder="Descreva o evento"
              className={`${inputClass} resize-none`} value={data.descricao}
              onChange={(e) => setData('descricao', e.target.value)} />
          </div>

          <div>
            <label htmlFor="local" className={labelClass}>Local</label>
            <input id="local" type="text" placeholder="Ex: Sede da ONG" className={inputClass}
              value={data.local} onChange={(e) => setData('local', e.target.value)} />
          </div>

          <div className="grid gap-6 sm:grid-cols-2">
            <div>
              <label className={labelClass}>Data de início *</label>
              <div className="flex gap-2">
                <input type="date" required
                  className="flex-1 rounded-xl border bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer"
                  value={data.data_inicio_date} onChange={(e) => setData('data_inicio_date', e.target.value)} />
                <input type="time" required
                  className="w-28 shrink-0 rounded-xl border bg-white px-3 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer"
                  value={data.data_inicio_time} onChange={(e) => setData('data_inicio_time', e.target.value)} />
              </div>
              {errors.data_inicio && <p className="mt-1 text-sm text-red-600">{errors.data_inicio}</p>}
            </div>
            <div>
              <label className={labelClass}>Data de fim *</label>
              <div className="flex gap-2">
                <input type="date" required
                  className="flex-1 rounded-xl border bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer"
                  value={data.data_fim_date} onChange={(e) => setData('data_fim_date', e.target.value)} />
                <input type="time"
                  className="w-28 shrink-0 rounded-xl border bg-white px-3 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer"
                  placeholder="--:--"
                  value={data.data_fim_time} onChange={(e) => setData('data_fim_time', e.target.value)} />
              </div>
              {errors.data_fim && <p className="mt-1 text-sm text-red-600">{errors.data_fim}</p>}
            </div>
          </div>

          <div>
            <label className={labelClass}>
              Limite de inscrição <span className="text-amber-900/40 font-normal">(opcional — inscrições fecham nesta data)</span>
            </label>
            <div className="flex gap-2">
              <input type="date"
                className="flex-1 rounded-xl border bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer"
                value={data.limite_inscricao_date} onChange={(e) => setData('limite_inscricao_date', e.target.value)} />
              <input type="time"
                className="w-28 shrink-0 rounded-xl border bg-white px-3 py-3 focus:outline-none focus:ring-2 focus:ring-amber-400 cursor-pointer"
                value={data.limite_inscricao_time} onChange={(e) => setData('limite_inscricao_time', e.target.value)} />
            </div>
            {errors.limite_inscricao && <p className="mt-1 text-sm text-red-600">{errors.limite_inscricao}</p>}
          </div>

          <div>
            <label htmlFor="limite" className={labelClass}>Limite de participantes <span className="text-amber-900/40 font-normal">(opcional)</span></label>
            <input id="limite" type="number" min={1} placeholder="Sem limite" className={inputClass}
              value={data.limite_participantes} onChange={(e) => setData('limite_participantes', e.target.value)} />
            {errors.limite_participantes && <p className="mt-1 text-sm text-red-600">{errors.limite_participantes}</p>}
          </div>

          {method === 'post' && (
            <div className="flex items-center gap-3 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3">
              <input
                id="inscrever_me"
                type="checkbox"
                className="cursor-pointer h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                checked={data.inscrever_me}
                onChange={(e) => setData('inscrever_me', e.target.checked)}
              />
              <label htmlFor="inscrever_me" className="cursor-pointer text-sm font-medium text-amber-900">
                Inscrever-me neste evento ao criar
              </label>
            </div>
          )}
        </div>

        <div className="flex flex-col gap-3 border-t bg-white px-8 py-6 sm:flex-row sm:items-center sm:justify-between md:px-12">
          <Link href={backHref}
            className="inline-flex cursor-pointer items-center justify-center gap-2 rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
            <ArrowLeft className="size-4" aria-hidden />
            Voltar
          </Link>
          <button type="submit" disabled={processing}
            className="inline-flex cursor-pointer items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70">
            <Check className="size-4" aria-hidden />
            {processing ? 'Salvando...' : 'Salvar'}
          </button>
        </div>
      </form>
    </div>
  )
}