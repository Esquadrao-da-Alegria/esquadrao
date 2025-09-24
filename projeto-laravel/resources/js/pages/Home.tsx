import React, { useEffect } from 'react';

// Importar CSS globais
import '../../css/footer.css';
import '../../css/navbar.css';
import '../../css/styles.css';

const Home: React.FC = () => {
    // Scripts que precisavam do <script> inline, como seu index.js
    useEffect(() => {
        // Se você tinha funções globais, pode chamá-las aqui
        // Por exemplo, se no index.js havia uma função enviarFormulario:
        const enviarFormulario = () => {
            console.log('Formulário enviado!');
        };

        // Torne global se quiser chamar via button inline
        (window as any).enviarFormulario = enviarFormulario;
    }, []);

    return (
        <div className="container-global">
            {/* Navbar */}
            <header>
                <nav className="navbar navbar-expand-lg fixed-top">
                    <div className="container">
                        <a
                            href="#"
                            className="navbar-brand d-flex align-items-center"
                        >
                            <img
                                src="/assets/images/logo-colorida.png"
                                alt="Logo Esquadrão"
                            />
                        </a>
                        <button
                            className="navbar-toggler navbar-light"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#navbar-links"
                            aria-controls="navbar-links"
                            aria-expanded="false"
                            aria-label="toggle navigation"
                        >
                            <span className="navbar-toggler-icon"></span>
                        </button>
                        <div
                            className="navbar-collapse justify-content-end collapse"
                            id="navbar-links"
                        >
                            <div className="navbar-nav">
                                <a
                                    href="/pages/conheca.html"
                                    className="nav-item nav-link"
                                    id="about-menu"
                                >
                                    Conheça
                                </a>
                                <a
                                    href="/pages/hospitais.html"
                                    className="nav-item nav-link"
                                    id="hospitals-menu"
                                >
                                    Hospitais
                                </a>
                                <a
                                    href="/pages/docacao.html"
                                    className="nav-item nav-link"
                                    id="partners-menu"
                                >
                                    Doação
                                </a>
                                <a
                                    href="#fale-conosco"
                                    className="nav-item nav-link"
                                    id="contact-menu"
                                >
                                    Fale Conosco
                                </a>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>

            {/* Banner Principal */}
            <div className="container-fluid banner">
                <div className="container">
                    <div className="row introducao">
                        <div className="col-md-6 d-flex justify-content-center align-items-center">
                            <img
                                className="image-palhacos img-fluid"
                                src="/assets/images/banner-principal.jpg"
                                alt="Banner integrantes Esquadrão da Alegria"
                            />
                        </div>
                        <div className="col-md-6 text-section">
                            <h1 className="rotate-left">
                                ESQUADRÃO DA ALEGRIA
                            </h1>
                            <p className="rotate-right">
                                Doutores e doutoras que atuam em hospitais por
                                meio dos últimos métodos besteirológicos
                                existentes para gerar o sorriso e o acolhimento
                            </p>
                            <img
                                className="image-logo img-fluid"
                                src="/assets/images/logo-colorida.png"
                                alt="Quem somos"
                            />
                        </div>
                    </div>
                </div>
                <div className="star top">★</div>
                <div className="star left-top">★</div>
                <div className="star left-bottom">★</div>
                <div className="star right">★</div>
                <div className="star bottom">★</div>
            </div>

            {/* ...continue com os outros blocos da mesma forma, convertendo class -> className, src -> /assets/... */}

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

export default Home;
