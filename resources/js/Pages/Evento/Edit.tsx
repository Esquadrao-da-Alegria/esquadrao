import PainelLayout from '@/layouts/PainelLayout'
import { type Evento, type User } from '@/types'
import { Link } from '@inertiajs/react'
import Form from './Form'
export default function Edit({ evento, responsaveis }: { evento: Evento; responsaveis: User[] }) { return <PainelLayout><div className="mx-auto max-w-4xl p-6 space-y-4"><Link href={`/eventos/${evento.id}`} className="text-sm text-amber-700 underline">← Voltar</Link><h1 className="text-2xl font-bold">Editar evento</h1><Form evento={evento} responsaveis={responsaveis} action={`/eventos/${evento.id}`} method="put" /></div></PainelLayout> }
