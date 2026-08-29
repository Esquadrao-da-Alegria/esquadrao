// UI
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { AlertCircleIcon } from 'lucide-react'

type ErrosBackend = Record<string, unknown> | string[] | undefined

interface ShowProps {
    erros: ErrosBackend
    titulo?: string
}

const listarErros = (erros: ErrosBackend): string[] => {
    if (!erros) {
        return []
    }

    if (Array.isArray(erros)) {
        return erros.filter(
            (erro): erro is string => typeof erro === 'string' && erro.length > 0,
        )
    }

    return Object.values(erros).flatMap((erro) => {
        if (typeof erro === 'string') {
            return erro.length > 0 ? [erro] : []
        }

        if (Array.isArray(erro)) {
            return erro.filter(
                (item): item is string => typeof item === 'string' && item.length > 0,
            )
        }

        return []
    })
}

const Show = ({ erros, titulo = 'Corrija os erros abaixo' }: ShowProps) => {
    const errosFormatados = Array.from(new Set(listarErros(erros)))

    if (errosFormatados.length === 0) {
        return null
    }

    return (
        <Card className="mb-4 border-destructive/40 py-4">
            <CardHeader className="px-4 pb-2">
                <CardTitle className="flex items-center gap-2 text-destructive">
                    <AlertCircleIcon className="size-4 shrink-0" aria-hidden />
                    {titulo}
                </CardTitle>
            </CardHeader>

            <CardContent className="px-4">
                <ul className="list-inside list-disc space-y-1 text-sm text-destructive">
                    {errosFormatados.map((erro, indice) => (
                        <li key={`${indice}-${erro}`}>{erro}</li>
                    ))}
                </ul>
            </CardContent>
        </Card>
    )
}

export default Show
