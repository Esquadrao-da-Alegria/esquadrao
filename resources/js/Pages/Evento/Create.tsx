import PainelLayout from '@/layouts/PainelLayout'
import { type User } from '@/types'
import Form from './Form'

export default function Create({ responsaveis }: { responsaveis: User[] }) {
  return (
    <PainelLayout>
      <section className="mx-auto w-full max-w-4xl px-4 py-16">
        <Form responsaveis={responsaveis} action="/eventos" method="post" backHref="/eventos" />
      </section>
    </PainelLayout>
  )
}