// REACT/INERTIA
import { Link } from '@inertiajs/react'

// UI
import { cn } from '@/lib/utils'
import { ArrowLeft } from 'lucide-react'

interface Props {
    href: string
    rotulo?: string
    className?: string
}

const Show = ({ href, rotulo = 'Voltar', className }: Props) => (
    <Link
        href={href}
        className={cn(
            'inline-flex items-center justify-center gap-2 rounded-full border-2 border-gray-200 px-6 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50',
            className,
        )}
    >
        <ArrowLeft className="size-4" aria-hidden />
        {rotulo}
    </Link>
)

export default Show
