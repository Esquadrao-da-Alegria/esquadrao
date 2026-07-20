import Form, { type RelatorioFormValues } from '@/components/Painel/Visita/Relatorio/Formulario/Form'
import PainelLayout from '@/layouts/PainelLayout'
import type { Visita } from '@/types'
import { Link, router, useForm } from '@inertiajs/react'
import type { FC } from 'react'

interface Props { visita: Visita; foraDoPrazoAviso: boolean }

const Create: FC<Props> = ({ visita, foraDoPrazoAviso }) => {
    const { data, setData, processing, errors } = useForm<RelatorioFormValues>({
        tipo_relatorio: '', resumo: '', feedback: '', ala_unidade: '', quartos_visitados: '', pessoas_impactadas: '', observacao_visitantes_externos: '', observacoes_gerais: '',
    })

    const submit = () => router.post(`/visitas/${visita.id}/relatorios`, {
        ...data,
        feedback: data.feedback || null,
        ala_unidade: data.ala_unidade || null,
        quartos_visitados: data.quartos_visitados === '' ? null : data.quartos_visitados,
        pessoas_impactadas: data.pessoas_impactadas === '' ? null : data.pessoas_impactadas,
        observacao_visitantes_externos: data.observacao_visitantes_externos || null,
        observacoes_gerais: data.observacoes_gerais || null,
    })

    return <PainelLayout><section className="mx-auto max-w-4xl px-4 py-12"><div className="rounded-3xl border bg-white p-8"><h1 className="mb-8 text-3xl font-bold text-amber-800">Criar relatório</h1><form onSubmit={(e) => { e.preventDefault(); submit() }} className="space-y-6"><Form data={data} errors={errors} foraDoPrazoAviso={foraDoPrazoAviso} onFieldChange={(campo, valor) => setData(campo, valor)} /><div className="flex justify-between"><Link href={`/visitas/${visita.id}/relatorios`} className="rounded-full border px-5 py-3">Voltar</Link><button disabled={processing} className="rounded-full border-2 border-amber-600 px-6 py-3 font-semibold text-amber-700">{processing ? 'Salvando...' : 'Salvar relatório'}</button></div></form></div></section></PainelLayout>
}

export default Create
