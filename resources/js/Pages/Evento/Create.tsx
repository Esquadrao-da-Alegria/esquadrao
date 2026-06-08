import PainelLayout from '@/layouts/PainelLayout'
import { Link, useForm } from '@inertiajs/react'
import React from 'react'
import { ArrowLeft, Check } from 'lucide-react'
import { index, store } from '@/routes/eventos'

const inputClass = 'mt-1 block w-full rounded-md border-gray-200 bg-white px-4 py-3 shadow-sm focus:border-amber-500 focus:ring-amber-500'
const labelClass = 'block text-sm font-medium text-gray-700'

const Create: React.FC<any> = ({ cidades, eventos_origem }) => {
  const { data, setData, post, processing, errors } = useForm({
    tipo: 'OFICINA',
    titulo: '',
    descricao: '',
    data: '',
    hora_inicio: '',
    hora_fim: '',
    local: '',
    cidade_id: '',
    status: 'AGENDADO',
    limite_vagas: null,
    feedback_habilitado: false,
    evento_origem_id: null,
  })

  const composeDateTime = (date: string, time: string) => {
    if (!date || !time) return ''
    return `${date} ${time}:00`
  }

  const handleSubmit = () => {
    post(store().url, {
      data: {
        ...data,
        data_inicio: composeDateTime(data.data, data.hora_inicio),
        data_fim: composeDateTime(data.data, data.hora_fim),
      },
    })
  }

  return (
    <PainelLayout>
      <section className="mx-auto w-full max-w-8xl px-4 py-16">
        <div className="flex justify-center">
          <div className="w-full max-w-7xl">
            <div className="overflow-hidden rounded-3xl border bg-white">
              <div className="p-8 md:p-12">
                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">Cadastrar evento</h2>

                {errors && Object.keys(errors).length > 0 && (
                  <div className="mb-4 rounded-lg border border-amber-200 bg-white p-4 text-amber-800">
                    <ul>
                      {Object.entries(errors).map(([campo, mensagem]) => (
                        <li key={campo}>{mensagem}</li>
                      ))}
                    </ul>
                  </div>
                )}

                <form id="evento-form" onSubmit={(e) => { e.preventDefault(); handleSubmit() }} className="space-y-6">
                  <div className="grid gap-4 md:grid-cols-2">
                    <div>
                      <label className={labelClass}>Tipo de evento</label>
                      <select
                        value={data.tipo}
                        onChange={(e) => setData('tipo', e.target.value)}
                        className={inputClass}
                      >
                        <option value="OFICINA">Oficina</option>
                        <option value="REUNIAO">Reunião</option>
                      </select>
                    </div>

                    <div>
                      <label className={labelClass}>Cidade</label>
                      <select
                        value={data.cidade_id}
                        onChange={(e) => setData('cidade_id', typeof e.target.value === 'number' ? String(e.target.value) : e.target.value)}
                        className={inputClass}
                      >
                        <option value="">-- Selecione --</option>
                        {cidades.map((cidade: any) => (
                          <option key={cidade.id} value={cidade.id}>
                            {cidade.nome}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div>
                      <label className={labelClass}>Data</label>
                      <input
                        type="date"
                        value={data.data}
                        onChange={(e) => setData('data', e.target.value)}
                        className={inputClass}
                      />
                    </div>

                    <div>
                      <label className={labelClass}>Hora de início</label>
                      <input
                        type="time"
                        value={data.hora_inicio}
                        onChange={(e) => setData('hora_inicio', e.target.value)}
                        className={inputClass}
                      />
                    </div>

                    <div>
                      <label className={labelClass}>Hora de término</label>
                      <input
                        type="time"
                        value={data.hora_fim}
                        onChange={(e) => setData('hora_fim', e.target.value)}
                        className={inputClass}
                      />
                    </div>

                    <div className="md:col-span-2">
                      <label className={labelClass}>Título</label>
                      <input
                        type="text"
                        value={data.titulo}
                        onChange={(e) => setData('titulo', e.target.value)}
                        className={inputClass}
                        placeholder="Digite o título do evento"
                      />
                    </div>

                    <div className="md:col-span-2">
                      <label className={labelClass}>Descrição</label>
                      <textarea
                        value={data.descricao}
                        onChange={(e) => setData('descricao', e.target.value)}
                        className={`${inputClass} min-h-[150px] resize-none`}
                        placeholder="Descreva o evento"
                      />
                    </div>

                    <div>
                      <label className={labelClass}>Local</label>
                      <input
                        type="text"
                        value={data.local}
                        onChange={(e) => setData('local', e.target.value)}
                        className={inputClass}
                        placeholder="Digite o local do evento"
                      />
                    </div>

                    <div>
                      <label className={labelClass}>Evento de origem</label>
                      <select
                        value={data.evento_origem_id ?? ''}
                        onChange={(e) => setData('evento_origem_id', e.target.value ? Number(e.target.value) : null)}
                        className={inputClass}
                      >
                        <option value="">Nenhum</option>
                        {eventos_origem.map((eventoOrigem: any) => (
                          <option key={eventoOrigem.id} value={eventoOrigem.id}>
                            {eventoOrigem.titulo}
                          </option>
                        ))}
                      </select>
                    </div>

                    <div>
                      <label className={labelClass}>Limite de vagas</label>
                      <input
                        type="number"
                        min={0}
                        value={data.limite_vagas ?? ''}
                        onChange={(e) => setData('limite_vagas', e.target.value === '' ? null : Number(e.target.value))}
                        className={inputClass}
                        placeholder="Deixe em branco se não houver limite"
                      />
                      <p className="mt-2 text-sm text-gray-500">Deixe em branco se não houver limite de vagas.</p>
                    </div>

                    <div className="flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-4">
                      <input
                        id="feedback_habilitado"
                        type="checkbox"
                        checked={data.feedback_habilitado}
                        onChange={(e) => setData('feedback_habilitado', e.target.checked)}
                        className="h-5 w-5 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                      />
                      <label htmlFor="feedback_habilitado" className="text-sm font-medium text-gray-700">
                        Habilitar feedback
                      </label>
                    </div>

                    <input type="hidden" name="status" value={data.status} />
                  </div>
                </form>
              </div>

              <div className="flex flex-col gap-3 border-t bg-white px-8 py-6 sm:flex-row sm:items-center sm:justify-between md:px-12">
                <Link href={index().url} className="inline-flex items-center justify-center gap-2 rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50">
                  <ArrowLeft className="size-4" aria-hidden />
                  Voltar
                </Link>

                <button type="submit" form="evento-form" disabled={processing} className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70">
                  <Check className="size-4" aria-hidden />
                  {processing ? 'Salvando...' : 'Salvar'}
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
    </PainelLayout>
  )
}

export default Create
