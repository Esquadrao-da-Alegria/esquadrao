import { Link } from '@inertiajs/react';
import { useState } from 'react';
import '../../css/footer.css';
import '../../css/styles.css';
import { home } from '@/routes';
import hospitais from '@/routes/hospitais';

interface Props {
    children: React.ReactNode;
}

const AppLayout: React.FC<Props> = ({ children }) => {
    const [isOpen, setIsOpen] = useState(false);

    return (
        <div className="container-global">
            {/* Navbar */}
            <header className="fixed top-0 z-50 w-full bg-white shadow">
                <nav className="container mx-auto flex items-center justify-between px-4 py-4 md:px-0">
                    {/* Logo */}
                    <Link href={home()} className="flex items-center">
                        <img
                            src="/assets/images/logo-colorida.png"
                            alt="Logo Esquadrão"
                            className="h-15 md:h-15"
                        />
                    </Link>

                    {/* Botão hamburger para mobile */}
                    <button
                        onClick={() => setIsOpen(!isOpen)}
                        className="flex items-center justify-center rounded-md p-2 text-gray-700 hover:text-gray-900 focus:ring-2 focus:ring-gray-500 focus:outline-none md:hidden"
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
                        className={`w-full flex-col transition-all duration-300 md:flex md:w-auto md:flex-row md:items-center ${
                            isOpen ? 'mt-4 flex md:mt-0' : 'hidden md:flex'
                        }`}
                    >
                        <Link
                            href="/pages/conheca.html"
                            className="block px-3 py-2 font-medium text-gray-700 no-underline hover:text-orange-500 md:inline-block"
                        >
                            Conheça
                        </Link>
                        <Link
                            href={hospitais.index()}
                            className="block px-3 py-2 font-medium text-gray-700 no-underline hover:text-orange-500 md:inline-block"
                        >
                            Hospitais
                        </Link>
                        <Link
                            href="/pages/docacao.html"
                            className="block px-3 py-2 font-medium text-gray-700 no-underline hover:text-orange-500 md:inline-block"
                        >
                            Doação
                        </Link>
                        <Link
                            href="#fale-conosco"
                            className="text-gray-800 no-underline hover:text-orange-500"
                        >
                            Fale Conosco
                        </Link>
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
                                    <a
                                        href="/pages/conheca.html"
                                        className="text-white"
                                    >
                                        Nossos Doutores
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/pages/hospitais.html"
                                        className="text-white"
                                    >
                                        Onde atuamos
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/pages/docacao.html"
                                        className="text-white"
                                    >
                                        Como Apoiar
                                    </a>
                                </li>
                                <li>
                                    <a
                                        href="/pages/docacao.html"
                                        className="text-white"
                                    >
                                        Fale Conosco
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div className="col-md-1 text-md-end mt-md-0 col-12 mt-4 text-center">
                            <div className="social-icons">
                                <a
                                    href="https://www.facebook.com/ongesquadraodaalegria"
                                    className="text-white"
                                    target="_blank"
                                >
                                    <i className="fab fa-facebook"></i>
                                </a>
                                <a
                                    href="https://www.instagram.com/ongesquadraodaalegria/"
                                    className="text-white"
                                    target="_blank"
                                >
                                    <i className="fab fa-instagram"></i>
                                </a>
                                <a
                                    href="https://www.linkedin.com/company/ong-esquadr%C3%A3o-da-alegria/"
                                    className="text-white"
                                    target="_blank"
                                >
                                    <i className="fab fa-linkedin"></i>
                                </a>
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

