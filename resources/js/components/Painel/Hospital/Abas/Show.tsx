import { Link } from '@inertiajs/react'
import { Building2, Target } from 'lucide-react'
import type { FC } from 'react'

interface Props {
    hospitalId: number
    abaAtiva: 'dados' | 'metas'
    podeEditarDados: boolean
}

const AbasHospital: FC<Props> = ({ hospitalId, abaAtiva, podeEditarDados }) => {
    const classe = (ativa: boolean) => `inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition ${
        ativa
            ? 'bg-amber-100 text-amber-900 ring-1 ring-amber-200'
            : 'text-amber-900/60 hover:bg-amber-50 hover:text-amber-900'
    }`

    return (
        <nav className="grid grid-cols-1 gap-2 border-b border-amber-100 pb-4 sm:flex sm:flex-wrap" aria-label="Configurações do hospital">
            {podeEditarDados ? (
                <Link href={`/hospitais/${hospitalId}/edit`} className={`${classe(abaAtiva === 'dados')} justify-center sm:justify-start`}>
                    <Building2 className="size-4" aria-hidden />
                    Dados do hospital
                </Link>
            ) : null}
            <Link href={`/hospitais/${hospitalId}/metas`} className={`${classe(abaAtiva === 'metas')} justify-center sm:justify-start`}>
                <Target className="size-4" aria-hidden />
                Metas de visitas
            </Link>
        </nav>
    )
}

export default AbasHospital
