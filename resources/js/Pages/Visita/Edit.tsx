import VisitaForm, { type VisitaFormValues } from '@/components/Painel/Visita/Formulario/Form'
import PainelLayout from '@/layouts/PainelLayout'
import { painelLabelClass } from '@/lib/painelFormFieldClasses'
import { extrairData, extrairHora } from '@/lib/visita'
import { index, update } from '@/routes/visitas'
import type { Hospital, User, Visita, VisitaParticipante } from '@/types'
import { Link, router, useForm } from '@inertiajs/react'
import { ArrowLeft, Check, FilePlus2, FileText } from 'lucide-react'
import { type FC, useMemo } from 'react'
import { toast } from 'react-toastify'

interface Props {
    hospitais: Hospital[]
    lideres: User[]
    visita: Visita
}

const labelTipoParticipacao = (tipo: VisitaParticipante['tipo_participacao']) =>
    tipo === 'palhaco' ? 'Palhaço' : 'Paisana'

const labelStatusParticipacao = (status: VisitaParticipante['status_participacao']) => {
    const labels: Record<VisitaParticipante['status_participacao'], string> = {
        confirmado: 'Confirmado',
        pendente: 'Pendente',
        cancelado: 'Cancelado',
        falta: 'Falta',
    }
    return labels[status] ?? status
}

const Edit: FC<Props> = ({ hospitais, lideres, visita }) => {
    const { data, setData, processing, errors } = useForm<VisitaFormValues>({
        hospital_id: visita.hospital_id,
        ala_unidade_id: visita.ala_unidade_id ?? null,
        data: extrairData(visita.inicio_em),
        hora_inicio: extrairHora(visita.inicio_em),
        hora_fim: extrairHora(visita.fim_em),
        tipo: visita.tipo,
        lider_id: visita.lider_id ?? '',
        status: visita.status,
        observacoes: visita.observacoes ?? '',
    })

    const handleFieldChange = <K extends keyof VisitaFormValues>(campo: K, valor: VisitaFormValues[K]) => {
        setData((prev) => ({ ...prev, [campo]: valor }))
    }

    const participantes = useMemo(
        () => (visita.participantes ?? [])
            .filter((p) => p.status_participacao !== 'cancelado')
            .sort((a, b) => (a.voluntario?.name ?? '').localeCompare(b.voluntario?.name ?? '')),
        [visita.participantes],
    )

    const handleSubmit = () => {
        if (!data.data || !data.hora_inicio || !data.hora_fim || !data.tipo || !data.lider_id || !data.status) {
            toast.error('Preencha todos os campos obrigatórios.')
            return
        }

        router.post(update({ visita: visita.id! }).url, { ...data, _method: 'put' })
    }

    return (
        <PainelLayout>
            <section className="mx-auto w-full max-w-8xl px-4 py-16">
                <div className="flex justify-center">
                    <div className="w-full max-w-7xl">
                        <div className="overflow-hidden rounded-3xl border bg-white">
                            <div className="p-8 md:p-12">
                                <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <h2 className="text-3xl font-bold text-amber-800 md:text-4xl">Alterar visita</h2>
                                    <div className="flex flex-wrap gap-2">
                                        <Link href={`/visitas/${visita.id}/relatorios`} className="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"><FileText className="size-4" />Ver relatórios</Link>
                                        <Link href={`/visitas/${visita.id}/relatorios/create`} className="inline-flex items-center gap-2 rounded-full border-2 border-amber-600 px-4 py-2 text-sm font-semibold text-amber-700 hover:bg-amber-50"><FilePlus2 className="size-4" />Criar relatório</Link>
                                    </div>
                                </div>

                                {errors && Object.keys(errors).length > 0 && <div className="mb-4 rounded-lg border border-amber-200 bg-white p-4 text-amber-800"><ul>{Object.entries(errors).map(([campo, mensagem]) => <li key={campo}>{mensagem}</li>)}</ul></div>}

                                <form id="visita-form" onSubmit={(e) => { e.preventDefault(); handleSubmit() }} className="space-y-6">
                                    <VisitaForm data={data} errors={errors} mode="edit" hospitais={hospitais} lideres={lideres} onFieldChange={handleFieldChange} />

                                    <div>
                                        <h3 className={painelLabelClass}>Participantes</h3>
                                        {participantes.length === 0 ? <p className="text-sm text-gray-400">Nenhum participante inscrito.</p> : <ul className="divide-y divide-gray-100 rounded-lg border border-gray-100 shadow-sm">{participantes.map((p) => <li key={p.id ?? p.voluntario_id} className="flex items-center justify-between px-3 py-2 text-sm"><span className="text-gray-900">{p.voluntario?.name ?? '—'}</span><span className="text-gray-500">{labelTipoParticipacao(p.tipo_participacao)} · {labelStatusParticipacao(p.status_participacao)}</span></li>)}</ul>}
                                    </div>
                                </form>
                            </div>

                            <div className="flex flex-col gap-3 border-t bg-white px-8 py-6 sm:flex-row sm:items-center sm:justify-between md:px-12">
                                <Link href={index().url} className="inline-flex items-center justify-center gap-2 rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"><ArrowLeft className="size-4" />Voltar</Link>
                                <button type="submit" form="visita-form" disabled={processing} className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"><Check className="size-4" />{processing ? 'Salvando...' : 'Salvar'}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </PainelLayout>
    )
}

export default Edit
