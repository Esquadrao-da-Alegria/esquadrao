import { type FC } from 'react'
import type { Visita } from '@/types/visita'
import CardShow from '@/components/Painel/Visita/Card/Show'

const DIAS_SEMANA = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']
const MAX_CARDS = 2

interface Props {
    visitas: Visita[]
    mes: string // YYYY-MM
    onSelecionarVisita: (visita: Visita) => void
    onAbrirListaCompleta: (dia: Date, visitas: Visita[]) => void
}

function gerarDiasDoMes(mes: string): Date[] {
    const [ano, mesNum] = mes.split('-').map(Number)
    const primeiroDia = new Date(ano, mesNum - 1, 1)
    const ultimoDia = new Date(ano, mesNum, 0)

    const dias: Date[] = []

    // Dias do mês anterior para completar a primeira semana
    const diaInicio = primeiroDia.getDay() // 0 = Dom
    for (let i = diaInicio - 1; i >= 0; i--) {
        const d = new Date(primeiroDia)
        d.setDate(d.getDate() - (i + 1))
        dias.push(d)
    }

    // Dias do mês atual
    for (let d = new Date(primeiroDia); d <= ultimoDia; d.setDate(d.getDate() + 1)) {
        dias.push(new Date(d))
    }

    // Dias do próximo mês para completar a última semana
    const restante = 7 - (dias.length % 7)
    if (restante < 7) {
        for (let i = 1; i <= restante; i++) {
            const d = new Date(ultimoDia)
            d.setDate(d.getDate() + i)
            dias.push(d)
        }
    }

    return dias
}

function mesmodia(d1: Date, d2: Date): boolean {
    return (
        d1.getFullYear() === d2.getFullYear() &&
        d1.getMonth() === d2.getMonth() &&
        d1.getDate() === d2.getDate()
    )
}

const Show: FC<Props> = ({ visitas, mes, onSelecionarVisita, onAbrirListaCompleta }) => {
    const dias = gerarDiasDoMes(mes)
    const [ano, mesNum] = mes.split('-').map(Number)
    const hoje = new Date()

    return (
        <div className="overflow-x-auto rounded-2xl border border-amber-100 bg-white shadow-sm">
            <div className="min-w-[650px]">
                {/* Cabeçalho dias da semana */}
                <div className="grid grid-cols-7 border-b border-amber-100">
                    {DIAS_SEMANA.map((dia) => (
                        <div
                            key={dia}
                            className="py-2 text-center text-xs font-semibold uppercase tracking-wide text-amber-700/70"
                        >
                            {dia}
                        </div>
                    ))}
                </div>

                {/* Grade de dias */}
                <div className="grid grid-cols-7">
                    {dias.map((dia, idx) => {
                        const ehMesAtual = dia.getMonth() === mesNum - 1 && dia.getFullYear() === ano
                        const visitasDoDia = visitas.filter((v) =>
                            mesmodia(new Date(v.inicio_em), dia),
                        )
                        const visitasVisiveis = visitasDoDia.slice(0, MAX_CARDS)
                        const overflow = visitasDoDia.length - MAX_CARDS

                        const ehHoje = mesmodia(dia, hoje)

                        return (
                            <div
                                key={idx}
                                className={`min-h-[6rem] border-b border-r border-gray-100 p-1.5 ${
                                    !ehMesAtual ? 'bg-gray-50/50' : ''
                                }`}
                            >
                                <span
                                    className={`mb-1 flex size-6 items-center justify-center rounded-full text-xs font-medium ${
                                        ehHoje
                                            ? 'bg-amber-500 text-white'
                                            : ehMesAtual
                                              ? 'text-gray-700'
                                              : 'text-gray-300'
                                    }`}
                                >
                                    {dia.getDate()}
                                </span>

                                <div className="space-y-0.5">
                                    {visitasVisiveis.map((v) => (
                                        <CardShow
                                            key={v.id}
                                            visita={v}
                                            onClick={() => onSelecionarVisita(v)}
                                        />
                                    ))}

                                    {overflow > 0 && (
                                        <button
                                            type="button"
                                            onClick={() => onAbrirListaCompleta(dia, visitasDoDia)}
                                            className="w-full rounded px-1 py-0.5 text-left text-xs text-amber-700 hover:bg-amber-50"
                                        >
                                            +{overflow} mais
                                        </button>
                                    )}
                                </div>
                            </div>
                        )
                    })}
                </div>
            </div>
        </div>
    )
}

export default Show
