import AppLayout from '@/layouts/AppLayout';
import { Link } from '@inertiajs/react';
import { useState } from 'react';
import '../../css/footer.css';
import '../../css/styles.css';
import hospitais from '@/routes/hospitais';

const Home: React.FC = () => {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        message: '',
    });

    const accessKey = '53b7a3e3-b32f-4b85-9be7-d8f176bed235';

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
    ) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const handleSubmit = () => {
        // Aqui você pode adicionar a lógica de envio do formulário
        console.log({ ...formData, accessKey });
        alert('Formulário enviado!');
    };

    return (
        <AppLayout>
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

            {/* Apoiadores */}
            <div
                className="container-informacoes p-3"
                style={{ width: '100%' }}
            >
                <div className="container">
                    <div className="row align-items-center text-center">
                        <div className="col-md-3 col-12">
                            <h3>Apoiadores</h3>
                        </div>
                        <div className="col-md-3 col-12">
                            <a
                                href="https://nfg.sefaz.rs.gov.br/site/index.aspx"
                                target="_blank"
                            >
                                <img
                                    src="./assets/images/logo-nfg.jpg"
                                    alt="Nota Fiscal Gaúcha"
                                    className="img-fluid d-block mx-auto"
                                    style={{ maxWidth: '150px' }}
                                ></img>
                            </a>
                        </div>
                        <div className="col-md-3 col-12">
                            <a
                                href="https://www.sicredi.com.br/home/"
                                target="_blank"
                            >
                                <img
                                    src="./assets/images/logo-sicredi.jpg"
                                    alt="Sicredi"
                                    className="img-fluid d-block mx-auto"
                                    style={{ maxWidth: '150px' }}
                                ></img>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {/* Como apoiar */}
            <div
                className="container-fluid"
                style={{ backgroundColor: '#ECFFF0' }}
            >
                <div className="como-apoiar-container container">
                    <div className="row align-items-center">
                        {/* Imagem do lado esquerdo */}
                        <div className="col-md-6">
                            <img
                                src="./assets/images/como-apoiar.png"
                                alt="Palhaça"
                                className="img-fluid como-apoiar-img"
                            />
                        </div>

                        {/* Conteúdo do lado direito */}
                        <div className="col-md-6">
                            <h2>
                                Como <span className="apoiar">apoiar</span> o
                                <br />
                                Esquadrão da Alegria
                            </h2>

                            {/* Informações */}
                            <div className="row mt-4">
                                <div className="col-md-4">
                                    <div className="info-card">
                                        <span className="number">+95</span>
                                        <p>Voluntários</p>
                                    </div>
                                </div>

                                <div className="col-md-4">
                                    <div className="info-card">
                                        <span className="number">+45mil</span>
                                        <p>Pessoas impactadas/ano</p>
                                    </div>
                                </div>

                                <div className="col-md-4">
                                    <div className="info-card">
                                        <span className="number">+900</span>
                                        <p>Visitas/ano</p>
                                    </div>
                                </div>
                            </div>

                            {/* Botão */}
                            <div className="mt-4">
                                <a href="./pages/docacao.html">
                                    <button
                                        className="btn btn-custom"
                                        style={{
                                            borderRadius: '15px',
                                            fontSize: '1.2rem',
                                        }}
                                    >
                                        Saiba mais
                                    </button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {/* Conheça */}
            <section id="conheca" className="container py-5">
                <div className="row align-items-center">
                    <div className="col-md-6 position-relative">
                        <h2 className="title-overlay">
                            CONHEÇA
                            <br />
                            NOSSOS
                            <br />
                            DOUTORES
                        </h2>
                        <img
                            className="conheca-img img-fluid"
                            src="./assets/images/conheca-palhacos.png"
                            alt="Imagem dos Doutores"
                        />
                    </div>

                    <div className="col-md-6">
                        <p className="description">
                            Nossos doutores besteirologistas são pessoas comuns,
                            das mais diferentes áreas de formação, que atuam na
                            sociedade como estudantes, profissionais e
                            empresários, entre outros, que dedicam parte do seu
                            tempo a nossa causa.
                        </p>
                        <a href="./pages/conheca.html">
                            <button
                                className="btn btn-conheca mt-3"
                                style={{ borderRadius: '15px' }}
                            >
                                Saiba mais
                            </button>
                        </a>
                    </div>
                </div>
            </section>

            {/* Hospitais */}
            <div className="container my-5">
                <h2 className="hospitais-title">ONDE ATUAMOS</h2>
                <div className="row">
                    <div className="col-md-4 mb-4">
                        <div className="city-card">
                            <img
                                src="./assets/images/porto-alegre.png"
                                alt="Porto Alegre"
                                className="img-fluid"
                            />
                            <Link
                                href={hospitais.index()}
                                className="card-content d-flex align-items-center"
                            >
                                <p className="city-text">Porto Alegre</p>
                                <span className="arrow">&gt;</span>
                            </Link>
                        </div>
                    </div>

                    <div className="col-md-4 mb-4">
                        <div className="city-card">
                            <img
                                src="./assets/images/santa-maria.png"
                                alt="Santa Maria"
                                className="img-fluid"
                            />
                            <a
                                href="./pages/hospitais.html"
                                className="card-content d-flex align-items-center"
                            >
                                <p className="city-text">Santa Maria</p>
                                <span className="arrow">&gt;</span>
                            </a>
                        </div>
                    </div>

                    <div className="col-md-4 mb-4">
                        <div className="city-card">
                            <img
                                src="./assets/images/pelotas.png"
                                alt="Pelotas"
                                className="img-fluid"
                            />
                            <a
                                href="./pages/hospitais.html"
                                className="card-content d-flex align-items-center"
                            >
                                <p className="city-text">Pelotas</p>
                                <span className="arrow">&gt;</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {/* Nossa História */}
            <div
                className="nossa-história"
                style={{ marginTop: '175px', height: '500px' }}
            >
                <div className="row align-items-center justify-content-between">
                    {/* Imagem da esquerda */}
                    <div className="col text-start">
                        <img
                            src="./assets/images/noosa-historia-esquerda.png"
                            alt="Nossa história"
                            className="img-fluid img-esquerda"
                        />
                    </div>

                    {/* Texto central */}
                    <div className="col texto-central">
                        <h3
                            className="titulo-historia mb-5"
                            style={{ textAlign: 'center', marginTop: '100px' }}
                        >
                            NOSSA HISTÓRIA
                        </h3>
                        <p className="mb-5" style={{ marginTop: '90px' }}>
                            Um grupo que começa com o desejo de transformar
                            espaços através da palhaçaria
                        </p>
                        <a href="./pages/conheca.html">
                            <button
                                type="button"
                                className="btn btn-historia mt-3"
                                style={{
                                    width: '200px',
                                    borderRadius: '15px',
                                    fontSize: '1.2rem',
                                }}
                            >
                                Saiba mais
                            </button>
                        </a>
                    </div>

                    {/* Imagem da direita */}
                    <div className="col text-end">
                        <img
                            src="./assets/images/nossa-historia-direita.png"
                            alt="Nossa história"
                            className="img-fluid img-direita"
                        />
                    </div>
                </div>
            </div>

            {/* Formulário */}
            <div className="container my-5" id="fale-conosco">
                <div className="row justify-content-center">
                    <div className="col-lg-8">
                        <div className="contact-form rounded p-4">
                            <div className="form-container d-flex flex-wrap">
                                {/* Formulário */}
                                <div className="col-md-6">
                                    <h2 className="mb-4">Fale Conosco</h2>
                                    <form
                                        id="meuFormulario"
                                        onSubmit={(e) => e.preventDefault()}
                                    >
                                        <div className="mb-3">
                                            <label
                                                htmlFor="nome"
                                                className="form-label"
                                            >
                                                Nome
                                            </label>
                                            <input
                                                type="text"
                                                name="name"
                                                className="form-control"
                                                id="nome"
                                                required
                                                placeholder="Digite seu nome"
                                                value={formData.name}
                                                onChange={handleChange}
                                            />
                                        </div>

                                        <div className="mb-3">
                                            <label
                                                htmlFor="email"
                                                className="form-label"
                                            >
                                                Email
                                            </label>
                                            <input
                                                type="email"
                                                name="email"
                                                className="form-control"
                                                id="email"
                                                required
                                                placeholder="Digite seu email"
                                                value={formData.email}
                                                onChange={handleChange}
                                            />
                                        </div>

                                        <div className="mb-3">
                                            <label
                                                htmlFor="mensagem"
                                                className="form-label"
                                            >
                                                Mensagem
                                            </label>
                                            <textarea
                                                className="form-control"
                                                name="message"
                                                id="mensagem"
                                                rows={5}
                                                required
                                                placeholder="Digite sua mensagem"
                                                value={formData.message}
                                                onChange={handleChange}
                                            ></textarea>
                                        </div>

                                        <button
                                            type="button"
                                            className="btn btn-submit"
                                            onClick={handleSubmit}
                                        >
                                            Enviar
                                        </button>

                                        <input
                                            type="hidden"
                                            name="accessKey"
                                            value={accessKey}
                                        />
                                    </form>
                                </div>

                                {/* Imagem lateral */}
                                <div className="col-md-6 d-none d-md-flex align-items-stretch p-0">
                                    <img
                                        src="assets/images/form-illustration.png"
                                        alt="Imagem"
                                        className="img-fluid"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default Home;
