import PainelLayout from '@/layouts/PainelLayout'
import { type User } from '@/types'
import { Link } from '@inertiajs/react'
import Form from './Form'
export default function Create({ responsaveis }: { responsaveis: User[] }) { return <PainelLayout><div className="mx-auto max-w-4xl p-6 space-y-4"><Link href="/eventos" className="text-sm text-amber-700 underline">← Voltar</Link><h1 className="text-2xl font-bold">Novo evento</h1><Form responsaveis={responsaveis} action="/eventos" method="post" /></div></PainelLayout> }
