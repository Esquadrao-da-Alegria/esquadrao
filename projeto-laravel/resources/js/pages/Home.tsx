import AppLayout from '@/layouts/AppLayout';
import hospitais from '@/routes/hospitais';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

const Home: React.FC = () => {
    const [formData, setFormData] = useState({
        name: '',
        email: '',
        message: '',
    });

    const accessKey = '53b7a3e3-b32f-4b85-9be7-d8f176bed235';

    const supporters = [
        {
            id: 1,
            name: 'Nota Fiscal Gaúcha',
            logo: './assets/images/logo-nfg.jpg',
            url: 'https://nfg.sefaz.rs.gov.br/site/index.aspx',
        },
        {
            id: 2,
            name: 'Sicredi',
            logo: './assets/images/logo-sicredi.jpg',
            url: 'https://www.sicredi.com.br/home/',
        },
    ];

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
            <div className="relative w-full overflow-hidden bg-gradient-to-br from-yellow-100 via-pink-100 to-purple-100 py-12 md:py-20">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="flex flex-col items-center gap-12 lg:flex-row">
                        {/* Imagem */}
                        <div className="flex flex-1 justify-center">
                            <div className="relative">
                                <div className="absolute -inset-6 rounded-3xl bg-gradient-to-r from-blue-200 to-purple-200 opacity-50 blur-xl"></div>
                                <img
                                    className="relative w-full max-w-md rounded-2xl shadow-2xl"
                                    src="/assets/images/banner-principal.jpg"
                                    alt="Banner integrantes Esquadrão da Alegria"
                                />
                            </div>
                        </div>

                        {/* Texto */}
                        <div className="flex-1 text-center lg:text-left">
                            <h1 className="mb-6 -rotate-2 transform text-4xl font-black md:text-5xl lg:text-6xl">
                                <span className="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                                    ESQUADRÃO DA ALEGRIA
                                </span>
                            </h1>

                            <p className="mb-8 rotate-1 transform text-lg leading-relaxed text-gray-700 md:text-xl">
                                Doutores e doutoras que atuam em hospitais por
                                meio dos últimos métodos besteirológicos
                                existentes para gerar o sorriso e o acolhimento
                            </p>

                            <div className="flex justify-center lg:justify-start">
                                <img
                                    className="w-48 opacity-90 transition-opacity duration-300 hover:opacity-100 md:w-56"
                                    src="/assets/images/logo-colorida.png"
                                    alt="Logo Esquadrão da Alegria"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Estrelas decorativas */}
                <div className="absolute top-10 left-10 animate-pulse text-2xl text-yellow-400">
                    ★
                </div>
                <div
                    className="absolute top-20 right-20 animate-bounce text-xl text-purple-400"
                    style={{ animationDelay: '0.5s' }}
                >
                    ★
                </div>
                <div
                    className="absolute bottom-20 left-20 animate-pulse text-lg text-pink-400"
                    style={{ animationDelay: '1s' }}
                >
                    ★
                </div>
                <div
                    className="absolute right-10 bottom-10 animate-bounce text-2xl text-blue-400"
                    style={{ animationDelay: '1.5s' }}
                >
                    ★
                </div>
                <div
                    className="absolute top-1/2 left-1/4 animate-pulse text-xl text-green-400"
                    style={{ animationDelay: '2s' }}
                >
                    ★
                </div>
            </div>

            {/* Apoiadores */}
            <div className="w-full bg-gradient-to-r from-blue-50 via-purple-50 to-pink-100 py-8 md:py-12">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="flex flex-col items-center justify-center gap-8 md:flex-row md:gap-16">
                        {/* Título */}
                        <div className="text-center md:text-left">
                            <h3 className="mb-2 text-2xl font-bold text-gray-800 md:text-3xl">
                                Apoiadores
                            </h3>
                            <div className="mx-auto h-1 w-16 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 md:mx-0"></div>
                        </div>

                        {/* Logos dos apoiadores */}
                        <div className="flex flex-wrap items-center justify-center gap-8 md:gap-12">
                            {supporters.map((supporter) => (
                                <a
                                    key={supporter.id}
                                    href={supporter.url}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="group transform transition-all duration-300 hover:scale-105"
                                >
                                    <div className="rounded-2xl border border-gray-100 bg-white p-4 shadow-lg transition-shadow duration-300 hover:shadow-xl">
                                        <img
                                            src={supporter.logo}
                                            alt={supporter.name}
                                            className="h-16 w-auto object-contain filter transition-all duration-300 group-hover:grayscale-0"
                                        />
                                    </div>
                                </a>
                            ))}
                        </div>
                    </div>

                    {/* Elementos decorativos sutis */}
                    <div className="mt-10 flex justify-center gap-3">
                        {[
                            ['💙', 'blue-400'],
                            ['💜', 'purple-400'],
                            ['💛', 'yellow-400'],
                        ].map(([emoji, color], index) => (
                            <div
                                key={index}
                                className={`h-8 w-8 rounded-full bg-${color} bg-opacity-20 flex animate-bounce items-center justify-center text-sm`}
                                style={{ animationDelay: `${index * 0.3}s` }}
                            >
                                {emoji}
                            </div>
                        ))}
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

            {/* Fale Conosco  */}
            <section
                id="fale-conosco"
                className="mx-auto w-full max-w-6xl px-4 py-16 md:py-24"
            >
                <div className="flex justify-center">
                    <div className="w-full max-w-4xl">
                        <div className="overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50 to-purple-50 shadow-lg">
                            <div className="flex flex-col lg:flex-row">
                                {/* Formulário */}
                                <div className="flex-1 p-8 md:p-12">
                                    <h2 className="mb-8 text-3xl font-bold text-gray-900 md:text-4xl">
                                        Fale Conosco
                                    </h2>

                                    <form
                                        onSubmit={(e) => e.preventDefault()}
                                        className="space-y-6"
                                    >
                                        {/* Nome */}
                                        <div>
                                            <label
                                                htmlFor="nome"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Nome
                                            </label>
                                            <input
                                                type="text"
                                                name="name"
                                                id="nome"
                                                required
                                                placeholder="Digite seu nome"
                                                value={formData.name}
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>

                                        {/* Email */}
                                        <div>
                                            <label
                                                htmlFor="email"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Email
                                            </label>
                                            <input
                                                type="email"
                                                name="email"
                                                id="email"
                                                required
                                                placeholder="Digite seu email"
                                                value={formData.email}
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>

                                        {/* Mensagem */}
                                        <div>
                                            <label
                                                htmlFor="mensagem"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Mensagem
                                            </label>
                                            <textarea
                                                name="message"
                                                id="mensagem"
                                                rows={5}
                                                required
                                                placeholder="Digite sua mensagem"
                                                value={formData.message}
                                                onChange={handleChange}
                                                className="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>

                                        {/* Botão Enviar */}
                                        <button
                                            type="button"
                                            onClick={handleSubmit}
                                            className="w-full transform bg-gradient-to-r from-blue-500 to-purple-500 px-6 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
                                        >
                                            Enviar Mensagem
                                        </button>
                                    </form>
                                </div>

                                {/* Imagem lateral */}
                                <div className="hidden flex-1 items-center justify-center bg-gradient-to-br from-blue-100 to-purple-100 p-8 lg:flex">
                                    <div className="relative">
                                        <div className="absolute -inset-6 rounded-2xl bg-gradient-to-r from-blue-200 to-purple-200 opacity-50 blur-lg"></div>
                                        <img
                                            src="/assets/images/conheca_2.png"
                                            alt="Ilustração de contato"
                                            className="relative h-full w-full rounded-2xl shadow-lg"
                                        />

                                        {/* Elementos decorativos */}
                                        <div className="absolute -top-4 -right-4 h-8 w-8 animate-pulse rounded-full bg-yellow-300 opacity-60"></div>
                                        <div
                                            className="absolute -bottom-4 -left-4 h-6 w-6 animate-bounce rounded-full bg-pink-300 opacity-60"
                                            style={{ animationDelay: '1s' }}
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </AppLayout>
    );
};

export default Home;
