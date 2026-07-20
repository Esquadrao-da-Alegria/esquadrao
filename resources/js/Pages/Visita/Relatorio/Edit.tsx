import Form, { type RelatorioFormValues } from '@/components/Painel/Visita/Relatorio/Formulario/Form'
import PainelLayout from '@/layouts/PainelLayout'
import type { Visita } from '@/types'
import { Link, router, useForm } from '@inertiajs/react'
import type { FC } from 'react'

interface Relatorio extends RelatorioFormValues { id: number; fora_do_prazo: boolean }
interface Props { visita: Visita; relatorio: Relatorio; foraDoPrazoAviso: boolean }

const Edit: FC<Props> = ({ visita, relatorio, foraDoPrazoAviso }) => {
    const { data, setData, processing, errors } = useForm<RelatorioFormValues>({
        tipo_relatorio: relatorio.tipo_relatorio,
        resumo: relatorio.resumo,
        feedback: relatorio.feedback ?? '',
        ala_unidade: relatorio.ala_unidade ?? '',
        quartos_visitados: relatorio.quartos_visitados ?? '',
        pessoas_impactadas: relatorio.pessoas_impactadas ?? '',
        observacao_visitantes_externos: relatorio.observacao_visitantes_externos ?? '',
        observacoes_gerais: relatorio.observacoes_gerais ?? '',
    })

    const submit = () => router.put(`/visitas/${visita.id}/relatorios/${relatorio.id}`, {
        ...data,
        feedback: data.feedback || null,
        ala_unidade: data.ala_unidade || null,
        quartos_visitados: data.quartos_visitados === '' ? null : data.quartos_visitados,
        pessoas_impactadas: data.pessoas_impactadas === '' ? null : data.pessoas_impactadas,
        observacao_visitantes_externos: data.observacao_visitantes_externos || null,
        observacoes_gerais: data.observacoes_gerais || null,
    })

    return <PainelLayout><section className="mx-auto max-w-4xl px-4 py-12"><div className="rounded-3xl border bg-white p-8"><h1 className="mb-8 text-3xl font-bold text-amber-800">Editar relatório</h1><form onSubmit={(e) => { e.preventDefault(); submit() }} className="space-y-6"><Form data={data} errors={errors} foraDoPrazoAviso={foraDoPrazoAviso} onFieldChange={(campo, valor) => setData(campo, valor)} /><div className="flex justify-between"><Link href={`/visitas/${visita.id}/relatorios/${relatorio.id}`} className="rounded-full border px-5 py-3">Voltar</Link><button disabled={processing} className="rounded-full border-2 border-amber-600 px-6 py-3 font-semibold text-amber-700">{processing ? 'Salvando...' : 'Salvar alterações'}</button></div></form></div></section></PainelLayout>
}

export default Edit
