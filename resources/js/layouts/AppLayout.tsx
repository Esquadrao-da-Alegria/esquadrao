import { home } from '@/routes'
import conheca from '@/routes/conheca'
import doacoes from '@/routes/doacoes'
import fale_conosco from '@/routes/fale_conosco'
import onde_atuamos from '@/routes/onde_atuamos'
import { Link, usePage } from '@inertiajs/react'
import { useEffect, useState } from 'react'
import { SharedData } from '@/types'
import { toastErro, toastSucesso } from '@/lib/utils/toast'

interface Props {
  children: React.ReactNode
}

const AppLayout: React.FC<Props> = ({ children }) => {
  const [isOpen, setIsOpen] = useState(false)
  const { props } = usePage<SharedData>()

  useEffect(() => {
    const mensagemSucesso = props.mensagem_sucesso
    const mensagemErro = props.mensagem_erro

    if (mensagemSucesso) toastSucesso(mensagemSucesso)
    if (mensagemErro) toastErro(mensagemErro)
  }, [props.mensagem_sucesso, props.mensagem_erro])

  return (
    <div className="min-h-screen flex flex-col bg-white text-gray-900">
      {/* Navbar */}
      <header className="fixed top-0 z-50 w-full bg-white shadow">
        <nav className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
          {/* Logo */}
          <Link href={home()} className="flex items-center">
            <img
              src="/assets/images/logo-colorida.png"
              alt="Logo Esquadrão"
              className="h-16 md:h-20"
            />
          </Link>

          {/* Links */}
          <div
            className={`${isOpen
              ? 'absolute left-0 top-20 w-full rounded-2xl border border-gray-100 bg-white/95 p-6 shadow-2xl backdrop-blur-lg flex flex-col items-center gap-3 md:static md:flex md:w-auto md:flex-row md:gap-1 md:border-none md:bg-transparent md:p-0 md:shadow-none'
              : 'hidden md:flex md:flex-row md:items-center md:gap-1'
              } transition-all duration-300`}
          >
            <Link
              href={conheca.index()}
              className="rounded-xl px-5 py-3 font-medium text-gray-700 transition hover:text-red-600 hover:bg-red-50 md:hover:bg-transparent"
            >
              Conheça
            </Link>
            <Link
              href={onde_atuamos.index()}
              className="rounded-xl px-5 py-3 font-medium text-gray-700 transition hover:text-purple-600 hover:bg-purple-50 md:hover:bg-transparent"
            >
              Hospitais
            </Link>
            <Link
              href={doacoes.index()}
              className="rounded-xl px-5 py-3 font-medium text-gray-700 transition hover:text-green-600 hover:bg-green-50 md:hover:bg-transparent"
            >
              Doação
            </Link>
            <Link
              href={fale_conosco.index()}
              className="rounded-xl px-5 py-3 font-medium text-gray-700 transition hover:text-orange-600 hover:bg-orange-50 md:hover:bg-transparent"
            >
              Fale Conosco
            </Link>
          </div>

          {/* Botão Mobile */}
          <button
            onClick={() => setIsOpen(!isOpen)}
            className="flex items-center justify-center rounded-xl p-2 text-gray-600 transition hover:bg-red-50 hover:text-red-600 focus:outline-none md:hidden"
          >
            <svg
              className="h-6 w-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              {isOpen ? (
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M6 18L18 6M6 6l12 12"
                />
              ) : (
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth={2}
                  d="M4 6h16M4 12h16M4 18h16"
                />
              )}
            </svg>
          </button>
        </nav>
      </header>

      {/* Conteúdo */}
      <main className="flex-1 pt-24">{children}</main>

      {/* Footer */}
      <footer className="mt-24 bg-[#ED1B24] py-10 text-white">
        <div className="mx-auto max-w-7xl px-6">
          {/* Conteúdo do footer */}
        </div>
      </footer>
    </div>
  )
}

export default AppLayout
