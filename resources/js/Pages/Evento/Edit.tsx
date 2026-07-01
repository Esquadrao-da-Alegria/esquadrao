import PainelLayout from '@/layouts/PainelLayout'
import { type Cidade, type Evento, type User } from '@/types'
import Form from './Form'

export default function Edit({ evento, responsaveis, cidades }: { evento: Evento; responsaveis: User[]; cidades: Cidade[] }) {
  return (
    <PainelLayout>
      <section className="mx-auto w-full max-w-4xl px-4 py-16">
        <Form evento={evento} responsaveis={responsaveis} cidades={cidades} action={`/eventos/${evento.id}`} method="put" backHref={`/eventos/${evento.id}`} />
      </section>
    </PainelLayout>
  )
}
