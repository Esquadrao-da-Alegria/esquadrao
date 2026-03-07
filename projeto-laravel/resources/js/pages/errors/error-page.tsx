import { Head, Link } from '@inertiajs/react'

interface Props {
  status: number
}

export default function ErrorPage({ status }: Props) {

  const titles: Record<number, string> = {
    403: 'Acesso negado',
    404: 'Página não encontrada',
    500: 'Erro interno',
    503: 'Serviço indisponível',
  }

  const messages: Record<number, string> = {
    403: 'Você não tem permissão para acessar esta página.',
    404: 'A página que você procura não existe.',
    500: 'Algo deu errado no servidor.',
    503: 'O serviço está temporariamente indisponível.',
  }

  return (
    <>
      <Head title={`${status} - ${titles[status]}`} />

      <div className="flex min-h-screen items-center justify-center bg-zinc-100 dark:bg-zinc-900">

        <div className="text-center">

          <h1 className="text-7xl font-bold text-zinc-800 dark:text-zinc-100">
            Ops!
          </h1>

          <p className="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
            {messages[status]}
          </p>

          <Link
            href="/"
            className="mt-6 inline-block rounded-lg bg-red-400 px-5 py-2 text-white"
          >
            Voltar para home
          </Link>

        </div>

      </div>
    </>
  )
}