import PainelLayout from '@/layouts/PainelLayout'
import { type Cidade, type User } from '@/types'
import Form from './Form'

export default function Create({ responsaveis, cidades }: { responsaveis: User[]; cidades: Cidade[] }) {
  return (
    <PainelLayout>
      <section className="mx-auto w-full max-w-4xl px-4 py-16">
        <Form responsaveis={responsaveis} cidades={cidades} action="/eventos" method="post" backHref="/eventos" />
      </section>
    </PainelLayout>
  )
}
