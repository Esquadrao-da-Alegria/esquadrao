import PainelLayout from '@/layouts/PainelLayout'
import { type Evento, type User } from '@/types'
import Form from './Form'

export default function Edit({ evento, responsaveis }: { evento: Evento; responsaveis: User[] }) {
  return (
    <PainelLayout>
      <section className="mx-auto w-full max-w-4xl px-4 py-16">
        <Form evento={evento} responsaveis={responsaveis} action={`/eventos/${evento.id}`} method="put" backHref={`/eventos/${evento.id}`} />
      </section>
    </PainelLayout>
  )
}