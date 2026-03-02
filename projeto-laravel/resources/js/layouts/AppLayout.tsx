import { home } from '@/routes';
import conheca from '@/routes/conheca';
import doacoes from '@/routes/doacoes';
import fale_conosco from '@/routes/fale_conosco';
import onde_atuamos from '@/routes/onde_atuamos';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import login from '@/routes/login';
import type { User } from '@/types/user';
import AuthRole from '@/components/AuthRole'



interface AuthProps {
    user: User | null;
}

interface InertiaPageProps {
    auth?: AuthProps; 
    [key: string]: any;
}

interface Props {
    children: React.ReactNode;
}


const AppLayout: React.FC<Props> = ({ children }) => {
    const [isOpen, setIsOpen] = useState(false);


    const { props } = usePage<InertiaPageProps>();
    const user = props.auth?.user;

    return (
        <div className="container-global top-0 z-50 w-full bg-white/80 backdrop-blur-md shadow-md ring-1 ring-gray-200">
            {/* Navbar */}
            <header className="fixed top-0 z-50 w-full bg-white/80 backdrop-blur-md shadow-md ring-1 ring-gray-200">
                <nav className="mx-auto flex max-w-6xl items-center justify-between p-6">
                    {/* Logo */}
                    <Link href={home()} className="flex items-center">
                        <img
                            src="/assets/images/logo-colorida.png"
                            alt="Logo Esquadrão"
                            className="h-15 md:h-20"
                        />
                    </Link>

                    {/* Botão hamburger para mobile */}
                    <button
                        onClick={() => setIsOpen(!isOpen)}
                        className="md:hidden rounded-xl p-3 text-gray-600 transition hover:bg-pink-50 hover:text-pink-600 focus:outline-none focus:ring-2 focus:ring-pink-200"
                    >
                        <svg
                            className="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg"
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

                    {/* Menu links */}
                    <div
                        className={`w-full flex-col transition-all duration-500 md:flex md:w-auto md:flex-row md:items-center ${
                            isOpen
                                ? 'mt-6 flex rounded-2xl border border-gray-100 bg-white/95 p-6 shadow-2xl backdrop-blur-lg md:mt-0 md:border-none md:bg-transparent md:p-0 md:shadow-none md:backdrop-blur-none'
                                : 'hidden md:flex'
                        }`}
                    >
                        <Link
                            href={conheca.index()}
                            className="block rounded-xl px-6 py-4 font-medium text-gray-700 no-underline transition-all duration-300 hover:bg-blue-50 hover:text-blue-600 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent"
                        >
                            Conheça
                        </Link>

                        <Link
                            href={onde_atuamos.index()}
                            className="block rounded-xl px-6 py-4 font-medium text-gray-700 no-underline transition-all duration-300 hover:bg-purple-50 hover:text-purple-600 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent"
                        >
                            Hospitais
                        </Link>

                        <Link
                            href={doacoes.index()}
                            className="block rounded-xl px-6 py-4 font-medium text-gray-700 no-underline transition-all duration-300 hover:bg-green-50 hover:text-green-600 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent"
                        >
                            Doação
                        </Link>

                        <Link
                            href={fale_conosco.index()}
                            className="block rounded-xl px-6 py-4 font-medium text-gray-700 no-underline transition-all duration-300 hover:bg-orange-50 hover:text-orange-600 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent"
                        >
                            Fale Conosco
                        </Link>

                        
                        <AuthRole role='diretor'>
                        
                        <Link
                            href={'user-management'}
                            className="block rounded-xl px-6 py-4 font-medium text-gray-700 no-underline transition-all 
                            duration-300 hover:bg-orange-50 hover:text-orange-600 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent">
                            Gerenciador de Voluntários
                        </Link>

                        <Link
                            href={'send-invitation'}
                            className="block rounded-xl px-6 py-4 font-medium text-gray-700 no-underline transition-all 
                            duration-300 hover:bg-orange-50 hover:text-orange-600 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent">
                            Enviar Convite
                        </Link>
                
                        </AuthRole> 

                        {user&&(
                            <Link
                                href={'perfil'}
                                method="get"
                                as="button"
                                className="group block rounded-xl px-6 py-4 font-medium text-gray-800 no-underline transition-all duration-300 hover:bg-red-50 hover:text-red-700 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent"
                            >
                                <span className="flex items-center gap-2">
                                    <span className="h-2 w-2 rounded-full bg-red-600 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                                    Olá, {user.name}!
                                </span>
                            </Link>
                            )
                        }  
                        {user&&(
                            <Link
                                href={'logout'}
                                method="post"
                                as="button"
                                className="block rounded-xl px-6 py-4 font-medium text-red-600 no-underline transition-all duration-300 hover:bg-red-50 hover:text-red-700 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent"
                            >
                                <span className="flex items-center gap-2">
                                    <span className="h-2 w-2 rounded-full bg-red-400 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                                    Sair
                                </span>
                            </Link>
                            )
                        }      
                          
                        
                            {!user 
                            &&(
                                <Link
                                    href={'login'}
                                    className="block rounded-xl px-6 py-4 font-medium text-gray-700 no-underline transition-all duration-300 hover:bg-orange-50 hover:text-orange-600 md:mx-1 md:px-4 md:py-2 md:hover:bg-transparent"
                                    >
                                    <span className="flex items-center gap-2">
                                        <span className="h-2 w-2 rounded-full bg-red-400 opacity-0 transition-opacity duration-300 group-hover:opacity-100"></span>
                                        Login
                                    </span>
                                </Link>

                            )
                        }
                    </div>
                </nav>
            </header>

            {/* Conteúdo da página */}
            <main className="pt-24">{children}</main>

            {/* Footer */}
            <footer
                className="bg-danger py-5 text-white"
                style={{ backgroundColor: '#ED1B24', marginTop: '150px' }}
            >
                <div className="position-relative container mx-auto">
                    <div className="row justify-content-center align-items-center">
                        <div className="col-md-3 text-md-start col-12 text-center">
                            <img
                                src="/assets/images/logo_png_branco.png"
                                alt="Esquadrão da Alegria"
                                className="footer-image"
                            />
                        </div>

                        <div className="col-md-4 col-12 text-center">
                            <h5 className="mb-3" style={{ fontSize: '1.2rem' }}>
                                Esquadrão da Alegria
                            </h5>
                            <ul className="list-unstyled">
                                <li>
                                    <Link
                                        href={conheca.index()}
                                        className="text-white"
                                    >
                                        Nossos Doutores
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href={onde_atuamos.index()}
                                        className="text-white"
                                    >
                                        Onde atuamos
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href={doacoes.index()}
                                        className="text-white"
                                    >
                                        Como Apoiar
                                    </Link>
                                </li>
                                <li>
                                    <Link
                                        href={fale_conosco.index()}
                                        className="text-white"
                                    >
                                        Fale Conosco
                                    </Link>
                                </li>
                            </ul>
                        </div>

                        <div className="col-md-1 text-md-end mt-md-0 col-12 mt-4 text-center">
                            <div className="social-icons">
                                <Link
                                    href="https://www.facebook.com/ongesquadraodaalegria"
                                    className="text-white"
                                    target="_blank"
                                >
                                    <i className="fab fa-facebook"></i>
                                </Link>
                                <Link
                                    href="https://www.instagram.com/ongesquadraodaalegria/"
                                    className="text-white"
                                    target="_blank"
                                >
                                    <i className="fab fa-instagram"></i>
                                </Link>
                                <Link
                                    href="https://www.linkedin.com/company/ong-esquadr%C3%A3o-da-alegria/"
                                    className="text-white"
                                    target="_blank"
                                >
                                    <i className="fab fa-linkedin"></i>
                                </Link>
                            </div>
                        </div>

                        <div className="mt-5 text-center">
                            <p className="mb-0">© Esquadrão da Alegria</p>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    );
};

export default AppLayout;