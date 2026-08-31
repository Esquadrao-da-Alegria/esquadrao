// REACT
import { type FC, useState } from 'react'
import { router } from '@inertiajs/react'

// UI
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import PainelLayout from '@/layouts/PainelLayout'
import {
    painelSelectTriggerClass,
    painelTableBodyClass,
    painelTableClass,
    painelTableHeadClass,
    painelTableRowClass,
    painelTableTdClass,
    painelTableThClass,
    painelTableWrapperClass,
} from '@/lib/painelFormFieldClasses'
import { Calendar, Lock, Unlock } from 'lucide-react'

interface MesLiberacao {
    mes: number
    liberado: boolean
    editavel: boolean
}

interface Props {
    ano: number
    meses: MesLiberacao[]
}

const NOMES_MESES = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
]

const Index: FC<Props> = ({ ano, meses }) => {
    const [processandoMes, setProcessandoMes] = useState<number | null>(null)

    const handleAnoChange = (novoAno: number) => {
        router.get('/visitas/agenda-liberacao', { ano: novoAno }, { preserveScroll: true })
    }

    const handleAlternar = (mes: MesLiberacao) => {
        if (!mes.editavel || processandoMes !== null) {
            return
        }

        setProcessandoMes(mes.mes)

        router.put('/visitas/agenda-liberacao', {
            ano,
            mes: mes.mes,
            liberado: !mes.liberado,
        }, {
            preserveScroll: true,
            onFinish: () => setProcessandoMes(null),
        })
    }

    return (
        <PainelLayout>
            <div className="mx-auto max-w-6xl px-5 py-8 sm:px-6 lg:px-8">
                <header className="mb-5">
                    <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                        Liberar agendas
                    </h1>
                    <p className="mt-1 text-sm text-amber-900/55">
                        Controle em quais meses visitas hospitalares podem ser cadastradas na sua cidade-base.
                    </p>
                </header>

                <section className="mb-5 flex flex-col gap-3 sm:flex-row">
                    <div className="sm:w-40">
                        <Select
                            value={String(ano)}
                            onValueChange={(valor) => handleAnoChange(Number(valor))}
                        >
                            <SelectTrigger className={painelSelectTriggerClass}>
                                <div className="flex items-center gap-2 truncate">
                                    <Calendar className="size-4 shrink-0 text-muted-foreground" aria-hidden />
                                    <SelectValue placeholder="Ano" />
                                </div>
                            </SelectTrigger>
                            <SelectContent>
                                {[ano - 1, ano, ano + 1].map((opcao) => (
                                    <SelectItem key={opcao} value={String(opcao)}>
                                        {opcao}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </section>

                <div className={painelTableWrapperClass}>
                    <table className={`${painelTableClass} min-w-[560px]`}>
                        <thead className={painelTableHeadClass}>
                            <tr>
                                <th className={painelTableThClass}>Ação</th>
                                <th className={painelTableThClass}>Mês</th>
                                <th className={painelTableThClass}>Situação</th>
                            </tr>
                        </thead>
                        <tbody className={painelTableBodyClass}>
                            {meses.map((item) => {
                                const carregando = processandoMes === item.mes

                                return (
                                    <tr
                                        key={item.mes}
                                        className={painelTableRowClass}
                                    >
                                        <td className={painelTableTdClass}>
                                            {item.liberado && item.editavel ? (
                                                <button
                                                    type="button"
                                                    onClick={() => handleAlternar(item)}
                                                    disabled={carregando}
                                                    className="inline-flex shrink-0 items-center gap-2 rounded-full border-2 border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 shadow-md transition hover:bg-red-100 disabled:opacity-60"
                                                >
                                                    <Lock className="size-4" aria-hidden />
                                                    {carregando ? 'Bloqueando...' : 'Bloquear'}
                                                </button>
                                            ) : (
                                                <button
                                                    type="button"
                                                    onClick={() => handleAlternar(item)}
                                                    disabled={!item.editavel || carregando || item.liberado}
                                                    className="inline-flex shrink-0 items-center gap-2 rounded-full border-2 border-green-600 bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-green-700 disabled:opacity-60"
                                                >
                                                    <Unlock className="size-4" aria-hidden />
                                                    {carregando ? 'Liberando...' : 'Liberar'}
                                                </button>
                                            )}
                                        </td>
                                        <td className={`${painelTableTdClass} font-medium text-foreground`}>
                                            {NOMES_MESES[item.mes - 1]}
                                        </td>
                                        <td className={painelTableTdClass}>
                                            <span
                                                className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ${
                                                    item.liberado
                                                        ? 'bg-green-50 text-green-800 ring-green-200'
                                                        : 'bg-muted text-muted-foreground ring-border'
                                                }`}
                                            >
                                                <span
                                                    className={`size-1.5 rounded-full ${
                                                        item.liberado ? 'bg-green-500' : 'bg-muted-foreground/50'
                                                    }`}
                                                    aria-hidden
                                                />
                                                {item.liberado ? 'Liberado' : 'Bloqueado'}
                                            </span>
                                            {!item.editavel ? (
                                                <span className="mt-1.5 block text-xs text-muted-foreground">
                                                    Somente leitura
                                                </span>
                                            ) : null}
                                        </td>
                                    </tr>
                                )
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </PainelLayout>
    )
}

export default Index
