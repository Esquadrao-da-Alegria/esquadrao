import MarketingLayout from '@/layouts/MarketingLayout';
import { useEffect, useState } from 'react';

const Index: React.FC = () => {
    const [slideSelecionado, setSlideSelecionado] = useState(0);

    const slides = [
        {
            id: 1,
            src: '../assets/images/conheca.jpeg',
            alt: 'Conheça nosso trabalho',
        },
        {
            id: 2,
            src: '../assets/images/conheca_2.png',
            alt: 'Nossas atividades',
        },
        {
            id: 3,
            src: '../assets/images/conheca_3.png',
            alt: 'Momentos especiais',
        },
    ];
    const linhaDoTempo = [
        { year: '2008', location: 'Santa Maria' },
        { year: '2012', location: 'Porto Alegre/Canoas' },
        { year: '2013', location: 'São Borja (Finalizado)' },
        { year: '2024', location: 'Pelotas' },
    ];

    const dadosDiretores = [
        { period: '2008 – 2016', name: 'Luciano Mai' },
        { period: '2017 – 2018', name: 'Laís Weber' },
        { period: '2019 – 2022', name: 'Eliane Saibt' },
        { period: '2023 – 2024', name: 'Bruna Naidon' },
    ];

    const motivacoes = [
        {
            id: 1,
            src: '../assets/images/nossa_motivacao.png',
            alt: 'Nossa motivação',
        },
        {
            id: 2,
            src: '../assets/images/nossa_motivacao2.png',
            alt: 'Nossa motivação',
        },
        {
            id: 3,
            src: '../assets/images/nossa_motivacao3.png',
            alt: 'Nossa motivação',
        },
    ];

    const servicosOferecidos = [
        {
            id: 1,
            icon: '🎭',
            title: 'Oficina de\nPalhaçaria',
            description:
                'Oficina de introdução à palhaçaria com duração de 6 horas, ministrada por 2 ou 3 integrantes do grupo.',
            color: 'from-purple-500 to-pink-500',
        },
        {
            id: 2,
            icon: '🤡',
            title: 'Intervenção de\nPalhaços',
            description:
                'Dois ou mais palhaços realizam intervenção em eventos como semanas comemorativas, SIPAT, palestras, etc.',
            color: 'from-blue-500 to-cyan-500',
        },
        {
            id: 3,
            icon: '💬',
            title: 'Palestra\nInterativa',
            description:
                'Benefícios do riso, funcionamento da ONG e a importância do doutor besteirologista no ambiente hospitalar.',
            color: 'from-green-500 to-teal-500',
        },
    ];

    const nextSlide = () => {
        setSlideSelecionado((prev) =>
            prev === slides.length - 1 ? 0 : prev + 1,
        );
    };

    const prevSlide = () => {
        setSlideSelecionado((prev) =>
            prev === 0 ? slides.length - 1 : prev - 1,
        );
    };

    const goToSlide = (index: number) => {
        setSlideSelecionado(index);
    };

    // Auto-rotate slides
    useEffect(() => {
        const interval = setInterval(() => {
            nextSlide();
        }, 5000);
        return () => clearInterval(interval);
    }, []);
    return (
        <MarketingLayout>
            {/* Caroussel FOTOS */}
            <div className="relative mx-auto w-[90%] overflow-hidden rounded-2xl p-2 shadow-xl">
                {/* Fundo animado simples */}
                <div className="absolute inset-0 animate-pulse bg-gradient-to-r from-blue-400 via-purple-500 to-pink-500 opacity-20"></div>

                {/* Efeitos flutuantes */}
                <div className="absolute -top-10 -left-10 h-32 w-32 animate-bounce rounded-full bg-yellow-300 opacity-20"></div>
                <div
                    className="absolute -right-8 -bottom-8 h-24 w-24 animate-bounce rounded-full bg-green-300 opacity-30"
                    style={{ animationDelay: '1.5s' }}
                ></div>
                {/* Carousel Container */}
                <div className="relative h-80 overflow-hidden rounded-xl md:h-96 lg:h-[700px]">
                    {/* Slides */}
                    {slides.map((slide, index) => (
                        <div
                            key={slide.id}
                            className={`absolute inset-0 flex items-center justify-center transition-opacity duration-500 ease-in-out ${
                                index === slideSelecionado
                                    ? 'opacity-100'
                                    : 'opacity-0'
                            }`}
                        >
                            <img
                                src={slide.src}
                                alt={slide.alt}
                                className="max-h-full max-w-full rounded-lg object-contain"
                            />
                        </div>
                    ))}

                    {/* Indicadores */}
                    <div className="absolute bottom-4 left-1/2 flex -translate-x-1/2 transform space-x-3">
                        {slides.map((_, index) => (
                            <button
                                key={index}
                                onClick={() => goToSlide(index)}
                                className={`h-3 w-3 rounded-full transition-all duration-300 ${
                                    index === slideSelecionado
                                        ? 'scale-125 bg-blue-500'
                                        : 'bg-white/70 hover:bg-white'
                                }`}
                                aria-label={`Ir para slide ${index + 1}`}
                            />
                        ))}
                    </div>

                    {/* Botões de navegação */}
                    <button
                        onClick={prevSlide}
                        className="absolute top-1/2 left-4 -translate-y-1/2 transform rounded-full bg-white/80 p-3 text-blue-600 shadow-lg transition-all duration-300 hover:scale-110 hover:bg-white focus:outline-none"
                        aria-label="Slide anterior"
                    >
                        <svg
                            className="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                    </button>

                    <button
                        onClick={nextSlide}
                        className="absolute top-1/2 right-4 -translate-y-1/2 transform rounded-full bg-white/80 p-3 text-blue-600 shadow-lg transition-all duration-300 hover:scale-110 hover:bg-white focus:outline-none"
                        aria-label="Próximo slide"
                    >
                        <svg
                            className="h-6 w-6"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </button>

                    {/* Contador de slides */}
                    <div className="absolute top-4 right-4 rounded-full bg-black/50 px-3 py-1 text-sm text-white backdrop-blur-sm">
                        {slideSelecionado + 1} / {slides.length}
                    </div>
                </div>

                {/* Efeito decorativo no fundo */}
                <div className="absolute -top-2 -right-2 -z-10 h-8 w-8 rounded-full bg-blue-400 opacity-20"></div>
                <div className="absolute -bottom-2 -left-2 -z-10 h-6 w-6 rounded-full bg-purple-400 opacity-20"></div>
            </div>

            {/* Linha do Tempo */}
            <section className="mx-auto w-full max-w-6xl px-4 py-14">
                {/* Cabeçalho */}
                <div className="mb-16 text-center">
                    <h2 className="mb-4 text-4xl font-bold text-gray-900 md:text-5xl">
                        NOSSA LINHA DO TEMPO
                    </h2>
                    <div className="mx-auto h-1 w-24 rounded-full bg-gradient-to-r from-blue-500 to-purple-500"></div>
                </div>

                {/* Linha do Tempo */}
                <div className="relative mb-20">
                    {/* Linha central */}
                    <div className="absolute top-0 bottom-0 left-1/2 w-2 -translate-x-1/2 transform bg-gradient-to-b from-blue-300 via-purple-300 to-pink-300"></div>

                    <div className="relative space-y-20">
                        {linhaDoTempo.map((item, index) => (
                            <div
                                key={item.year}
                                className="relative flex items-center justify-center"
                            >
                                {/* Ponto do ano */}
                                <div
                                    className={`relative z-10 flex flex-col items-center ${
                                        index % 2 === 0
                                            ? 'md:flex-row'
                                            : 'md:flex-row-reverse'
                                    }`}
                                >
                                    {/* Localização */}
                                    <div
                                        className={`mb-6 text-center md:mb-0 md:w-2/5 ${
                                            index % 2 === 0
                                                ? 'md:pr-12 md:text-right'
                                                : 'md:pl-12 md:text-left'
                                        }`}
                                    >
                                        <span className="inline-block rounded-xl border border-gray-100 bg-white px-6 py-4 text-xl font-semibold text-gray-800 shadow-2xl">
                                            {item.location}
                                        </span>
                                    </div>

                                    {/* Ano */}
                                    <div className="mx-8 flex flex-col items-center">
                                        <div className="hover:shadow-3xl flex h-32 w-32 transform items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-blue-500 to-purple-500 text-2xl font-bold text-white shadow-2xl transition-all duration-300 hover:scale-110">
                                            {item.year}
                                        </div>
                                    </div>

                                    {/* Espaço vazio para alternância */}
                                    <div className="hidden md:block md:w-2/5"></div>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Seção Diretores Gerais */}
                <div className="rounded-2xl border border-gray-100 bg-gradient-to-br from-blue-50 to-purple-50 p-8 shadow-lg md:p-12">
                    <div className="flex flex-col items-center gap-8 lg:flex-row">
                        {/* Imagem */}
                        <div className="flex-shrink-0">
                            <div className="relative">
                                <div className="absolute -inset-8 rounded-2xl bg-gradient-to-r from-blue-400 to-purple-400 opacity-30 blur-lg"></div>
                                <img
                                    src="../assets/images/diretor2.png"
                                    alt="Diretores Gerais"
                                    className="relative h-96 w-96 rounded-2xl bg-white object-contain p-4 shadow-lg md:h-[450px] md:w-[450px]"
                                />
                            </div>
                        </div>

                        {/* Seta decorativa */}
                        <div className="hidden flex-shrink-0 lg:block">
                            <div className="rotate-45 transform text-4xl text-purple-400">
                                ➜
                            </div>
                        </div>

                        {/* Informações dos Diretores */}
                        <div className="flex-1">
                            <h3 className="mb-6 text-center text-2xl font-bold text-gray-900 lg:text-left">
                                Diretores Gerais
                            </h3>
                            <div className="space-y-4">
                                {dadosDiretores.map((director, index) => (
                                    <div
                                        key={director.period}
                                        className="flex items-center gap-4 rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition-shadow duration-300 hover:shadow-md"
                                    >
                                        <div className="h-3 w-3 rounded-full bg-gradient-to-r from-blue-400 to-purple-400"></div>
                                        <div className="flex-1">
                                            <p className="text-gray-800">
                                                <strong className="text-blue-600">
                                                    {director.period}
                                                </strong>
                                                <span className="mx-2 text-gray-400">
                                                    →
                                                </span>
                                                {director.name}
                                            </p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Por que somos Palhaços */}
            <section className="mx-auto w-full max-w-6xl px-4 py-16 md:py-14">
                {/* Título principal */}
                <div className="mb-12 text-center">
                    <h1 className="mb-6 text-4xl font-bold text-gray-900 md:text-6xl">
                        <span className="mb-2 block text-2xl font-semibold text-blue-600 md:text-3xl">
                            POR QUE SOMOS
                        </span>
                        <span className="block bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-5xl text-transparent md:text-7xl">
                            PALHAÇOS?
                        </span>
                    </h1>

                    {/* Linha decorativa */}
                    <div className="mx-auto mb-8 h-1 w-24 rounded-full bg-gradient-to-r from-blue-400 to-purple-400"></div>
                </div>

                {/* Texto descritivo */}
                <div className="mb-16 text-center">
                    <p className="mx-auto max-w-4xl text-lg leading-relaxed text-gray-600 md:text-xl">
                        Além de proporcionar diversão e risadas, os palhaços
                        podem ser um instrumento valioso na promoção da saúde
                        mental e física, ajudando a aliviar o sofrimento de
                        pacientes e a melhorar sua qualidade de vida durante o
                        tratamento.
                    </p>
                </div>

                {/* Ícones e textos adicionais */}
                <div className="mx-auto max-w-4xl space-y-8">
                    {/* Primeiro item */}
                    <div className="flex items-start gap-6 rounded-2xl bg-gradient-to-r from-blue-50 to-purple-50 p-6 shadow-sm transition-shadow duration-300 hover:shadow-md">
                        <div className="flex-shrink-0">
                            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-yellow-400 to-orange-400 px-5 shadow-lg">
                                <span className="text-2xl">😊</span>
                            </div>
                        </div>
                        <div className="flex-1">
                            <p className="text-lg leading-relaxed text-gray-700">
                                O palhaço é capaz de transformar o ambiente
                                hospitalar, pois ele é exatamente o que o
                                paciente quer que ele seja.
                            </p>
                        </div>
                    </div>

                    {/* Segundo item */}
                    <div className="flex items-start gap-6 rounded-2xl bg-gradient-to-r from-pink-50 to-red-50 p-6 shadow-sm transition-shadow duration-300 hover:shadow-md">
                        <div className="flex-shrink-0">
                            <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-red-400 to-pink-500 px-5 shadow-lg">
                                <span className="text-2xl">❤️</span>
                                <span className="text-2xl">🤡</span>
                            </div>
                        </div>
                        <div className="flex-1">
                            <p className="text-xl leading-relaxed font-semibold text-gray-800">
                                Com amor e com palhaço, tudo é possível!
                            </p>
                        </div>
                    </div>
                </div>

                {/* Elementos decorativos sutis */}
                <div className="absolute top-1/4 left-10 h-6 w-6 animate-pulse rounded-full bg-yellow-300 opacity-20"></div>
                <div className="absolute top-1/2 right-20 h-4 w-4 animate-bounce rounded-full bg-purple-300 opacity-30"></div>
            </section>

            {/* Motivações */}
            <section className="mx-auto w-full max-w-7xl px-4 py-16 md:py-14">
                {/* Título */}
                <div className="mb-16 text-center">
                    <h2 className="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">
                        NOSSA MOTIVAÇÃO
                    </h2>
                    <div className="mx-auto h-1 w-20 rounded-full bg-gradient-to-r from-blue-500 to-purple-500"></div>
                </div>

                {/* Grid de imagens */}
                <div className="grid grid-cols-1 gap-8 md:grid-cols-3">
                    {motivacoes.map((motivation) => (
                        <div
                            key={motivation.id}
                            className="group relative flex transform items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-blue-50/50 to-purple-50/50 shadow-lg transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl"
                        >
                            {/* Container da imagem */}
                            <div className="relative flex h-80 w-full items-center justify-center p-4 md:h-96">
                                <img
                                    src={motivation.src}
                                    alt={motivation.alt}
                                    className="max-h-full max-w-full object-contain transition-transform duration-700 group-hover:scale-105"
                                />
                            </div>

                            {/* Elemento decorativo */}
                            <div className="absolute top-4 right-4 h-6 w-6 rounded-full bg-gradient-to-r from-blue-400 to-purple-400 opacity-0 transition-opacity delay-200 duration-500 group-hover:opacity-100"></div>
                        </div>
                    ))}
                </div>

                {/* Elementos decorativos sutis */}
                <div className="absolute top-1/3 left-4 h-8 w-8 animate-pulse rounded-full bg-yellow-300 opacity-20"></div>
                <div
                    className="absolute right-8 bottom-1/4 h-6 w-6 animate-bounce rounded-full bg-pink-300 opacity-30"
                    style={{ animationDelay: '1s' }}
                ></div>
            </section>

            {/* Como nos preparamos */}
            <section className="w-full bg-gradient-to-br from-gray-50 to-blue-50 py-16 md:py-24">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="flex flex-col items-center gap-12 lg:flex-row lg:items-center lg:gap-16">
                        {/* Texto */}
                        <div className="flex-1 text-center lg:text-left">
                            <h1 className="mb-8">
                                <span className="mb-2 block text-2xl font-semibold text-blue-600 md:text-3xl">
                                    COMO NOS
                                </span>
                                <span className="block bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-4xl font-bold text-transparent md:text-5xl lg:text-6xl">
                                    PREPARAMOS?
                                </span>
                            </h1>

                            <div className="mx-auto mb-8 h-1 w-20 bg-gradient-to-r from-blue-400 to-purple-400 lg:mx-0"></div>

                            <p className="max-w-2xl text-lg leading-relaxed text-gray-600 md:text-xl">
                                Aos voluntários, é oferecida 1 oficina por mês
                                e, para a permanência no grupo, o voluntário
                                deve ter participação em no mínimo 50% das
                                oficinas a cada semestre.
                            </p>

                            {/* Elementos decorativos */}
                            <div className="mt-8 flex gap-3">
                                {[
                                    ['🎭', 'bg-purple-100'],
                                    ['🌟', 'bg-yellow-100'],
                                    ['💫', 'bg-blue-100'],
                                ].map(([emoji, bgColor], index) => (
                                    <div
                                        key={index}
                                        className={`h-12 w-12 rounded-xl ${bgColor} flex items-center justify-center text-xl shadow-sm transition-transform duration-300 hover:scale-110`}
                                    >
                                        {emoji}
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Imagem */}
                        <div className="flex-1">
                            <div className="relative">
                                <div className="absolute -inset-4 rounded-2xl bg-gradient-to-r from-blue-200 to-purple-200 opacity-50 blur-lg"></div>
                                <img
                                    src="../assets/images/como_nos_preparamos.png"
                                    alt="Imagem ilustrativa"
                                    className="relative mx-auto w-full max-w-md rounded-2xl shadow-lg lg:mx-0"
                                />

                                {/* Elementos decorativos flutuantes */}
                                <div className="absolute -top-4 -right-4 h-8 w-8 animate-pulse rounded-full bg-yellow-300 opacity-60"></div>
                                <div
                                    className="absolute -bottom-4 -left-4 h-6 w-6 animate-bounce rounded-full bg-blue-300 opacity-60"
                                    style={{ animationDelay: '1s' }}
                                ></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Outros Serviços */}
            <section className="mx-auto w-full max-w-6xl px-4 py-16 md:py-24">
                {/* Título principal */}
                <div className="mb-16 text-center">
                    <h2 className="mb-4 text-3xl font-bold text-gray-900 md:text-4xl">
                        OUTROS SERVIÇOS QUE OFERECEMOS
                    </h2>
                    <div className="mx-auto h-1 w-24 rounded-full bg-gradient-to-r from-blue-500 to-purple-500"></div>
                </div>

                {/* Card principal */}
                <div className="relative rounded-3xl border border-gray-100 bg-gradient-to-br from-blue-50 to-purple-50 p-8 shadow-lg md:p-12">
                    {/* Grid de serviços */}
                    <div className="mb-12 grid grid-cols-1 gap-8 md:grid-cols-3">
                        {servicosOferecidos.map((service) => (
                            <div
                                key={service.id}
                                className="group transform rounded-2xl bg-white p-6 text-center shadow-sm transition-all duration-500 hover:-translate-y-2 hover:shadow-xl"
                            >
                                {/* Ícone */}
                                <div
                                    className={`inline-flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-r ${service.color} mb-6 text-3xl text-white shadow-lg transition-transform duration-300 group-hover:scale-110`}
                                >
                                    {service.icon}
                                </div>

                                {/* Título */}
                                <h3 className="mb-4 text-xl leading-tight font-bold whitespace-pre-line text-gray-800">
                                    {service.title}
                                </h3>

                                {/* Descrição */}
                                <p className="leading-relaxed text-gray-600">
                                    {service.description}
                                </p>
                            </div>
                        ))}
                    </div>

                    {/* Botão */}
                    <div className="text-center">
                        <a
                            href="../index.html#fale-conosco"
                            className="mx-auto inline-flex max-w-xs transform items-center justify-center rounded-full border-2 border-blue-600 px-8 py-4 text-lg font-semibold text-blue-600 shadow-sm transition-all duration-300 hover:scale-105 hover:bg-blue-600 hover:text-white hover:shadow-md"
                        >
                            Entre em Contato
                        </a>
                    </div>

                    {/* Elementos decorativos */}
                    <div className="absolute -top-4 -right-4 h-8 w-8 animate-pulse rounded-full bg-yellow-300 opacity-60"></div>
                    <div
                        className="absolute -bottom-4 -left-4 h-6 w-6 animate-bounce rounded-full bg-pink-300 opacity-60"
                        style={{ animationDelay: '1s' }}
                    ></div>
                </div>
            </section>
        </MarketingLayout>
    );
};

export default Index;
