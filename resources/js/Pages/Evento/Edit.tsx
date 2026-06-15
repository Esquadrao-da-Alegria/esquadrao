import PainelLayout from '@/layouts/PainelLayout'
import { Link, useForm } from '@inertiajs/react'
import { ArrowLeft, Check, MapPin, Plus, Search, X } from 'lucide-react'
import React, { useEffect, useRef, useState } from 'react'
import { index, update } from '@/routes/eventos'

const inputClass = 'mt-1 block w-full rounded-md border-gray-200 bg-white px-4 py-3 shadow-sm focus:border-amber-500 focus:ring-amber-500'
const labelClass = 'block text-sm font-medium text-gray-700'

interface NominatimResult {
  display_name: string
  lat: string
  lon: string
}

interface Usuario { id: number; name: string }

interface EventoFormData {
  tipo: string
  titulo: string
  descricao: string
  data: string
  hora_inicio: string
  hora_fim: string
  complemento: string
  local_latitude: number | null
  local_longitude: number | null
  cidade_id: number | string
  status: string
  limite_vagas: number | null
  feedback_habilitado: boolean
  responsaveis: number[]
}

const Edit: React.FC<{ evento: any; cidades: any[]; usuarios: Usuario[] }> = ({ evento, cidades, usuarios }) => {
  const formatDateValue = (value?: string) => {
    if (!value) return ''
    return value.slice(0, 10)
  }

  const formatTimeValue = (value?: string) => {
    if (!value) return ''
    return value.slice(11, 16)
  }

  const { data, setData, put, processing, errors } = useForm<EventoFormData>({
    tipo: evento?.tipo || 'OFICINA',
    titulo: evento?.titulo || '',
    descricao: evento?.descricao || '',
    data: formatDateValue(evento?.data_inicio),
    hora_inicio: formatTimeValue(evento?.data_inicio),
    hora_fim: formatTimeValue(evento?.data_fim),
    complemento: evento?.complemento || '',
    local_latitude: evento?.local_latitude ?? null,
    local_longitude: evento?.local_longitude ?? null,
    cidade_id: evento?.cidade_id || '',
    status: evento?.status || 'AGENDADO',
    limite_vagas: evento?.limite_vagas ?? null,
    feedback_habilitado: !!evento?.feedback_habilitado,
    responsaveis: evento?.responsaveis?.map((r: any) => r.voluntario_id) ?? [],
  })

  const hoje = new Date().toLocaleDateString('en-CA')

  const [mostrarDropdownResp, setMostrarDropdownResp] = useState(false)
  const [buscaResponsavel, setBuscaResponsavel] = useState('')
  const dropdownRespRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const handler = (e: MouseEvent) => {
      if (dropdownRespRef.current && !dropdownRespRef.current.contains(e.target as Node)) {
        setMostrarDropdownResp(false)
      }
    }
    document.addEventListener('mousedown', handler)
    return () => document.removeEventListener('mousedown', handler)
  }, [])

  const adicionarResponsavel = (id: number) => {
    setData('responsaveis', [...data.responsaveis, id])
    setBuscaResponsavel('')
    setMostrarDropdownResp(false)
  }

  const removerResponsavel = (id: number) => {
    setData('responsaveis', data.responsaveis.filter(r => r !== id))
  }

  const usuariosDisponiveis = usuarios.filter(u =>
    !data.responsaveis.includes(u.id) &&
    u.name.toLowerCase().includes(buscaResponsavel.toLowerCase())
  )

  const [buscaLocal, setBuscaLocal] = useState('')
  const [sugestoes, setSugestoes] = useState<NominatimResult[]>([])
  const [buscando, setBuscando] = useState(false)

  const buscarLocal = async () => {
    if (!buscaLocal.trim()) return
    setBuscando(true)
    try {
      const resp = await fetch(
        `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(buscaLocal)}&limit=5`,
        { headers: { 'Accept-Language': 'pt-BR' } },
      )
      const resultados: NominatimResult[] = await resp.json()
      setSugestoes(resultados)
    } catch {
      setSugestoes([])
    } finally {
      setBuscando(false)
    }
  }

  const selecionarLocal = (resultado: NominatimResult) => {
    setData({
      ...data,
      local_latitude: parseFloat(resultado.lat),
      local_longitude: parseFloat(resultado.lon),
    })
    setSugestoes([])
    setBuscaLocal(resultado.display_name)
  }

  const handleSubmit = () => {
    put(update(evento.id).url)
  }

  return (
    <PainelLayout>
      <section className="mx-auto w-full max-w-8xl px-4 py-16">
        <div className="flex justify-center">
          <div className="w-full max-w-7xl">
            <div className="overflow-hidden rounded-3xl border bg-white">
              <div className="p-8 md:p-12">
                <h2 className="mb-8 text-3xl font-bold text-amber-800 md:text-4xl">Editar evento</h2>

                {errors && Object.keys(errors).length > 0 && (
                  <div className="mb-4 rounded-lg border border-amber-200 bg-white p-4 text-amber-800">
                    <ul>
                      {Object.entries(errors).map(([campo, mensagem]) => (
                        <li key={campo}>{mensagem as string}</li>
                      ))}
                    </ul>
                  </div>
                )}

                <form id="evento-form" onSubmit={(e) => { e.preventDefault(); handleSubmit() }} className="space-y-6">
                  <div className="grid gap-4 md:grid-cols-2">
                    <div>
                      <label className={labelClass}>Tipo de evento</label>
                      <select value={data.tipo} onChange={(e) => setData('tipo', e.target.value)} className={inputClass}>
                        <option value="OFICINA">Oficina</option>
                        <option value="REUNIAO">Reunião</option>
                      </select>
                    </div>

                    <div>
                      <label className={labelClass}>Cidade</label>
                      <select
                        value={data.cidade_id}
                        onChange={(e) => setData('cidade_id', Number(e.target.value) || '')}
                        className={inputClass}
                      >
                        <option value="">-- Selecione --</option>
                        {cidades.map((cidade: any) => (
                          <option key={cidade.id} value={cidade.id}>{cidade.nome}</option>
                        ))}
                      </select>
                    </div>

                    <div>
                      <label className={labelClass}>Data</label>
                      <input type="date" min={hoje} value={data.data} onChange={(e) => setData('data', e.target.value)} className={inputClass} />
                    </div>

                    <div>
                      <label className={labelClass}>Hora de início</label>
                      <input type="time" value={data.hora_inicio} onChange={(e) => setData('hora_inicio', e.target.value)} className={inputClass} />
                    </div>

                    <div>
                      <label className={labelClass}>Hora de término</label>
                      <input
                        type="time"
                        value={data.hora_fim}
                        min={data.hora_inicio || undefined}
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

                    {/* Localização */}
                    <div className="md:col-span-2">
                      <label className={labelClass}>Local do evento</label>
                      <div className="mt-1 flex gap-2">
                        <input
                          type="text"
                          value={buscaLocal}
                          onChange={(e) => setBuscaLocal(e.target.value)}
                          onKeyDown={(e) => e.key === 'Enter' && (e.preventDefault(), buscarLocal())}
                          className={`${inputClass} mt-0 flex-1`}
                          placeholder="Buscar endereço (ex: Rua das Flores, São Paulo)"
                        />
                        <button
                          type="button"
                          onClick={buscarLocal}
                          disabled={buscando}
                          className="flex items-center gap-1 rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-50"
                        >
                          <Search size={14} /> {buscando ? 'Buscando...' : 'Buscar'}
                        </button>
                      </div>

                      {sugestoes.length > 0 && (
                        <ul className="mt-1 rounded-md border border-gray-200 bg-white shadow-sm">
                          {sugestoes.map((s, i) => (
                            <li
                              key={i}
                              onClick={() => selecionarLocal(s)}
                              className="flex cursor-pointer items-start gap-2 px-4 py-2 text-sm hover:bg-amber-50"
                            >
                              <MapPin size={14} className="mt-0.5 shrink-0 text-amber-600" />
                              {s.display_name}
                            </li>
                          ))}
                        </ul>
                      )}

                      {data.local_latitude && (
                        <div className="mt-2 flex items-start gap-2 rounded-md bg-amber-50 p-3 text-sm text-amber-800">
                          <MapPin size={14} className="mt-0.5 shrink-0" />
                          <span>{buscaLocal || 'Localização selecionada'}</span>
                        </div>
                      )}
                    </div>

                    <div className="md:col-span-2">
                      <label className={labelClass}>Complemento <span className="text-gray-400 font-normal">(opcional)</span></label>
                      <input
                        type="text"
                        value={data.complemento}
                        onChange={(e) => setData('complemento', e.target.value)}
                        className={inputClass}
                        placeholder="Sala 2, bloco B, portão lateral..."
                      />
                    </div>

                    {/* Responsáveis */}
                    <div className="md:col-span-2">
                      <label className={labelClass}>Responsáveis</label>
                      <div className="mt-1 flex flex-wrap items-center gap-2">
                        {data.responsaveis.map(id => {
                          const nome = usuarios.find(u => u.id === id)?.name ?? ''
                          return (
                            <span key={id} className="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1 text-sm font-medium text-amber-800">
                              {nome}
                              <button type="button" onClick={() => removerResponsavel(id)} className="ml-1 text-amber-500 hover:text-amber-800">
                                <X size={12} />
                              </button>
                            </span>
                          )
                        })}
                        <div className="relative" ref={dropdownRespRef}>
                          <button
                            type="button"
                            onClick={() => setMostrarDropdownResp(v => !v)}
                            className="inline-flex items-center gap-1 rounded-full border border-dashed border-gray-300 px-3 py-1 text-sm text-gray-500 hover:border-amber-400 hover:text-amber-600"
                          >
                            <Plus size={14} /> Adicionar
                          </button>
                          {mostrarDropdownResp && (
                            <div className="absolute z-10 mt-1 w-64 rounded-lg border border-gray-200 bg-white shadow-lg">
                              <div className="p-2">
                                <input
                                  type="text"
                                  value={buscaResponsavel}
                                  onChange={e => setBuscaResponsavel(e.target.value)}
                                  placeholder="Buscar por nome..."
                                  autoFocus
                                  className="w-full rounded-md border border-gray-200 px-3 py-2 text-sm focus:border-amber-500 focus:outline-none"
                                />
                              </div>
                              <ul className="max-h-48 overflow-y-auto">
                                {usuariosDisponiveis.length === 0
                                  ? <li className="px-4 py-2 text-sm text-gray-400">Nenhum usuário encontrado</li>
                                  : usuariosDisponiveis.map(u => (
                                    <li key={u.id} onClick={() => adicionarResponsavel(u.id)} className="cursor-pointer px-4 py-2 text-sm hover:bg-amber-50">
                                      {u.name}
                                    </li>
                                  ))
                                }
                              </ul>
                            </div>
                          )}
                        </div>
                      </div>
                    </div>

                    <div>
                      <label className={labelClass}>Limite de vagas</label>
                      <input
                        type="number"
                        min={1}
                        value={data.limite_vagas ?? ''}
                        onChange={(e) => setData('limite_vagas', e.target.value === '' ? null : Number(e.target.value))}
                        className={inputClass}
                        placeholder="Deixe em branco se não houver limite"
                      />
                      <p className="mt-1 text-sm text-gray-500">Deixe em branco se não houver limite de vagas.</p>
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
                <button
                  type="submit"
                  form="evento-form"
                  disabled={processing}
                  className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"
                >
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

export default Edit
