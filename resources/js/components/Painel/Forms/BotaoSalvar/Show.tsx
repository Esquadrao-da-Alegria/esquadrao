// REACT
import { type ButtonHTMLAttributes, type FC, type ReactNode } from 'react'

// UI
import { cn } from '@/lib/utils'
import { CircleCheck } from 'lucide-react'

interface Props extends ButtonHTMLAttributes<HTMLButtonElement> {
    salvando?: boolean
    rotulo?: string
    rotuloSalvando?: string
    tamanho?: 'padrao' | 'grande'
    children?: ReactNode
}

const Show: FC<Props> = ({
    salvando = false,
    rotulo = 'Salvar',
    rotuloSalvando = 'Salvando...',
    tamanho = 'padrao',
    children,
    className,
    disabled,
    type = 'button',
    ...props
}) => {
    const texto = children ?? (salvando ? rotuloSalvando : rotulo)

    return (
        <button
            type={type}
            disabled={disabled ?? salvando}
            className={cn(
                'inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-amber-600 font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:opacity-60',
                tamanho === 'grande' ? 'px-6 py-3 text-sm' : 'px-6 py-2.5 text-sm',
                className,
            )}
            {...props}
        >
            <CircleCheck className="size-4" aria-hidden />
            {texto}
        </button>
    )
}

export default Show
