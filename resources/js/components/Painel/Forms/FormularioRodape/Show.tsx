// REACT
import { type FC, type ReactNode } from 'react'

// UI
import BotaoVoltar from '@/components/Painel/Forms/BotaoVoltar/Show'
import { cn } from '@/lib/utils'

interface Props {
    voltarHref: string
    voltarRotulo?: string
    variante?: 'cartao' | 'pagina'
    className?: string
    inicio?: ReactNode
    salvar: ReactNode
}

const Show: FC<Props> = ({
    voltarHref,
    voltarRotulo,
    variante = 'cartao',
    className,
    inicio,
    salvar,
}) => (
    <div
        className={cn(
            'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between',
            variante === 'cartao'
                ? 'border-t bg-white px-8 py-6 md:px-12'
                : 'mt-6 border-t border-amber-200 pt-5',
            className,
        )}
    >
        <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
            <BotaoVoltar href={voltarHref} rotulo={voltarRotulo} />
            {inicio}
        </div>

        {salvar}
    </div>
)

export default Show
