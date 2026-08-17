import MarketingLayout from '@/layouts/MarketingLayout';
import conheca from '@/routes/conheca';
import doacoes from '@/routes/doacoes';
import onde_atuamos from '@/routes/onde_atuamos';
import { Link } from '@inertiajs/react';
import { useRef, useState } from 'react';
import { toast } from 'react-toastify';

interface Patrocinador {
    id: string;
    nome: string;
    site?: string;
    logo_path?: string;
    ativo: boolean;
    ordem_exibicao: number;
}

interface Props {
    patrocinadores?: Patrocinador[];
}

//finalizar o conserto do carrosel, falta os botoes e os elementos decorativos
const Home: React.FC<Props> = ({ patrocinadores = [] }) => {
    const carrosselRef = useRef<HTMLDivElement>(null);

    const scrollEsquerda = () => {
        if (carrosselRef.current) {
            carrosselRef.current.scrollBy({ left: -300, behavior: 'smooth' });
        }
    };

    const scrollDireita = () => {
        if (carrosselRef.current) {
            carrosselRef.current.scrollBy({ left: 300, behavior: 'smooth' });
        }
    };
    const [formData, setFormData] = useState({
        nome: '',
        email: '',
        mensagem: '',
        honeypot: '',
    });

    const accessKey = '53b7a3e3-b32f-4b85-9be7-d8f176bed235';

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
    ) => {
        const { name, value } = e.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
    };

    const resetarFormulario = () => {
        setFormData({
            nome: '',
            email: '',
            mensagem: '',
            honeypot: '',
        });
    };

    const handleSubmit = async () => {
        if (formData.honeypot) {
            toast.success('Mensagem enviada com sucesso!');
            resetarFormulario();
            return;
        }

        if (!formData.nome || !formData.email || !formData.mensagem) {
            toast.error('Por favor, preencha todos os campos.');
            return;
        }

        try {
            const url = 'https://api.staticforms.xyz/submit';
            const { honeypot, ...dadosEnviados } = formData;
            const dadosPost = { ...dadosEnviados, accessKey: accessKey };

            const options = {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(dadosPost),
            };

            const retorno = await fetch(url, options);

            if (!retorno.ok) throw new Error('Erro ao enviar a mensagem.');

            toast.success('Mensagem enviada com sucesso!');
            resetarFormulario();
        } catch (error) {
            console.error('Erro:', error);
            toast.error(
                'Ocorreu um erro ao enviar a mensagem. Tente novamente mais tarde.',
            );
        }
    };

    return (
        <MarketingLayout>
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

            {/* Apoiadores - Finalizar Carrossel */}
            <div className="w-full bg-gradient-to-r from-blue-50 via-purple-50 to-pink-100 py-8 md:py-16">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="flex flex-col items-center justify-center gap-10">
                        {/* Título */}
                        <div className="text-center">
                            <h3 className="mb-2 text-2xl font-bold text-gray-800 md:text-3xl">
                                Apoiadores
                            </h3>
                            <div className="mx-auto h-1 w-24 rounded-full bg-gradient-to-r from-blue-500 to-purple-500"></div>
                        </div>

                        {/* Carrossel Tailwind */}
                        <div className="w-full">
                            <div className="flex w-full snap-x snap-mandatory [scrollbar-width:none] justify-center gap-6 overflow-x-auto px-4 py-8 [-ms-overflow-style:none] sm:px-8 [&::-webkit-scrollbar]:hidden">
                                {patrocinadores.length > 0 ? (
                                    patrocinadores.map((patrocinador) => (
                                        <a
                                            key={patrocinador.id}
                                            href={patrocinador.site || '#'}
                                            target={
                                                patrocinador.site
                                                    ? '_blank'
                                                    : '_self'
                                            }
                                            rel="noopener noreferrer"
                                            className="group flex w-56 shrink-0 snap-center flex-col items-center justify-center transition-all duration-300 hover:-translate-y-2 md:w-64"
                                        >
                                            <div className="flex h-40 w-full items-center justify-center rounded-2xl border border-gray-100 bg-white p-6 shadow-md transition-shadow duration-300 group-hover:shadow-xl">
                                                {patrocinador.logo_path ? (
                                                    <img
                                                        src={
                                                            patrocinador.logo_path
                                                        }
                                                        alt={patrocinador.nome}
                                                        className="max-h-full max-w-full object-contain transition-transform duration-300 group-hover:scale-105"
                                                    />
                                                ) : (
                                                    <span className="text-center font-bold text-gray-400">
                                                        {patrocinador.nome}
                                                    </span>
                                                )}
                                            </div>
                                        </a>
                                    ))
                                ) : (
                                    <p className="w-full text-center text-gray-500 italic">
                                        Estamos em busca de parceiros incríveis
                                        para fazer parte do nosso time de
                                        apoiadores! Se você têm interesse em
                                        colaborar, entre em contato conosco!!
                                    </p>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Elementos decorativos sutis */}
                    <div className="mt-6 flex justify-center gap-3">
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
            <section className="bg-[#ECFFF0] py-16">
                <div className="mx-auto max-w-6xl px-6">
                    <div className="flex flex-col items-center gap-12 md:flex-row">
                        <div className="flex flex-1 justify-center">
                            <img
                                src="./assets/images/como-apoiar.png"
                                alt="Palhaça"
                                className="w-full max-w-md rounded-2xl shadow-md"
                            />
                        </div>

                        <div className="flex-1 text-center md:text-left">
                            <h2 className="text-3xl font-extrabold text-gray-800 md:text-4xl">
                                Como{' '}
                                <span className="text-green-600">apoiar ?</span>
                            </h2>

                            <div className="mt-8 grid grid-cols-1 gap-6 sm:grid-cols-3">
                                <div className="rounded-xl bg-white p-6 shadow-sm">
                                    <span className="block text-3xl font-bold text-green-600">
                                        +95
                                    </span>
                                    <p className="mt-1 text-gray-700">
                                        Voluntários
                                    </p>
                                </div>

                                <div className="rounded-xl bg-white p-6 shadow-sm">
                                    <span className="block text-3xl font-bold text-green-600">
                                        +45mil
                                    </span>
                                    <p className="mt-1 text-gray-700">
                                        Pessoas impactadas/ano
                                    </p>
                                </div>

                                <div className="rounded-xl bg-white p-6 shadow-sm">
                                    <span className="block text-3xl font-bold text-green-600">
                                        +900
                                    </span>
                                    <p className="mt-1 text-gray-700">
                                        Visitas/ano
                                    </p>
                                </div>
                            </div>

                            <div className="mt-8">
                                <Link href={doacoes.index()}>
                                    <button className="rounded-full bg-gradient-to-r from-green-400 to-green-600 px-8 py-3 font-semibold text-white shadow-md transition-all duration-300 hover:scale-105 hover:shadow-lg focus:ring-2 focus:ring-green-400 focus:ring-offset-2 focus:outline-none">
                                        Saiba mais
                                    </button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Conheça */}
            <section id="conheca" className="border-b border-gray-100 py-20">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="flex flex-col items-center gap-12 md:flex-row">
                        <div className="w-full md:w-1/2">
                            <img
                                className="h-auto w-full rounded-2xl shadow-md"
                                src="./assets/images/conheca-palhacos.png"
                                alt="Imagem dos Doutores"
                            />
                        </div>

                        <div className="w-full md:w-1/2">
                            <h2 className="mb-6 text-4xl leading-tight font-bold text-gray-800 md:text-5xl">
                                CONHEÇA <br /> NOSSOS <br /> DOUTORES
                            </h2>

                            <p className="text-lg leading-relaxed text-gray-700">
                                Nossos doutores besteirologistas são pessoas
                                comuns, das mais diferentes áreas de formação,
                                que atuam na sociedade como estudantes,
                                profissionais e empresários, entre outros, que
                                dedicam parte do seu tempo a nossa causa.
                            </p>

                            <Link href={conheca.index()}>
                                <button className="mt-6 rounded-[15px] bg-blue-600 px-8 py-3 font-medium text-white transition-colors duration-300 hover:bg-blue-700">
                                    Saiba mais
                                </button>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            {/* Hospitais */}
            <section
                id="hospitais"
                className="mx-auto mt-32 mb-20 max-w-6xl px-6"
            >
                <h2 className="mb-12 text-center text-3xl font-extrabold text-gray-900">
                    ONDE ATUAMOS
                </h2>

                <div className="grid gap-8 md:grid-cols-3">
                    <div className="overflow-hidden rounded-2xl shadow-md transition-transform duration-300 hover:scale-105">
                        <img
                            src="./assets/images/porto-alegre.png"
                            alt="Porto Alegre"
                            className="w-full object-cover"
                        />
                        <Link
                            href={onde_atuamos.index()}
                            className="group flex items-center justify-between bg-white px-6 py-4"
                        >
                            <p className="text-lg font-semibold text-gray-800">
                                Porto Alegre
                            </p>
                            <span className="text-2xl text-pink-500 transition-transform duration-200 group-hover:translate-x-1">
                                &gt;
                            </span>
                        </Link>
                    </div>

                    <div className="overflow-hidden rounded-2xl shadow-md transition-transform duration-300 hover:scale-105">
                        <img
                            src="./assets/images/santa-maria.png"
                            alt="Santa Maria"
                            className="w-full object-cover"
                        />
                        <Link
                            href={onde_atuamos.index()}
                            className="group flex items-center justify-between bg-white px-6 py-4"
                        >
                            <p className="text-lg font-semibold text-gray-800">
                                Santa Maria
                            </p>
                            <span className="text-2xl text-pink-500 transition-transform duration-200 group-hover:translate-x-1">
                                &gt;
                            </span>
                        </Link>
                    </div>

                    <div className="overflow-hidden rounded-2xl shadow-md transition-transform duration-300 hover:scale-105">
                        <img
                            src="./assets/images/pelotas.png"
                            alt="Pelotas"
                            className="w-full object-cover"
                        />
                        <Link
                            href={onde_atuamos.index()}
                            className="group flex items-center justify-between bg-white px-6 py-4"
                        >
                            <p className="text-lg font-semibold text-gray-800">
                                Pelotas
                            </p>
                            <span className="text-2xl text-pink-500 transition-transform duration-200 group-hover:translate-x-1">
                                &gt;
                            </span>
                        </Link>
                    </div>
                </div>
            </section>

            {/* Nossa História */}
            <section
                id="nossa-historia"
                className="mt-44 flex flex-col items-center justify-center overflow-hidden bg-white px-6 py-20 md:flex-row md:justify-between"
            >
                <div className="mb-12 flex flex-1 justify-start md:mb-0">
                    <img
                        src="./assets/images/noosa-historia-esquerda.png"
                        alt="Nossa história"
                        className="w-full max-w-sm md:max-w-md"
                    />
                </div>

                <div className="flex flex-1 flex-col items-center px-4 text-center">
                    <h3 className="mb-8 text-3xl font-extrabold text-gray-900 md:text-4xl">
                        NOSSA HISTÓRIA
                    </h3>
                    <p className="mb-10 text-lg text-gray-700">
                        Um grupo que começa com o desejo de transformar espaços
                        através da palhaçaria
                    </p>
                    <Link href={conheca.index()}>
                        <button className="rounded-full bg-gradient-to-r from-pink-500 to-purple-600 px-8 py-3 text-lg font-semibold text-white shadow-md transition-all duration-300 hover:scale-105 hover:shadow-lg focus:ring-2 focus:ring-pink-400 focus:ring-offset-2 focus:outline-none">
                            Saiba mais
                        </button>
                    </Link>
                </div>

                <div className="mt-12 flex flex-1 justify-end md:mt-0">
                    <img
                        src="./assets/images/nossa-historia-direita.png"
                        alt="Nossa história"
                        className="w-full max-w-sm md:max-w-md"
                    />
                </div>
            </section>

            {/* Fale Conosco */}
            <section
                id="fale-conosco"
                className="mx-auto w-full max-w-6xl px-4 py-16 md:py-24"
            >
                <div className="flex justify-center">
                    <div className="w-full max-w-4xl">
                        <div className="overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50 to-purple-50 shadow-xl">
                            <div className="flex flex-col lg:flex-row">
                                <div className="flex-1 p-8 md:p-12">
                                    <h2 className="mb-8 text-3xl font-extrabold text-gray-900 md:text-4xl">
                                        Fale Conosco
                                    </h2>

                                    <form
                                        onSubmit={(e) => e.preventDefault()}
                                        className="space-y-6"
                                    >
                                        <div
                                            className="hidden"
                                            aria-hidden="true"
                                        >
                                            <input
                                                type="text"
                                                name="honeypot"
                                                tabIndex={-1}
                                                autoComplete="off"
                                                value={formData.honeypot}
                                                onChange={handleChange}
                                            />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="nome"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Nome
                                            </label>
                                            <input
                                                type="text"
                                                id="nome"
                                                name="nome"
                                                required
                                                placeholder="Digite seu nome"
                                                value={formData.nome}
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                            />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="email"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Email
                                            </label>
                                            <input
                                                type="email"
                                                id="email"
                                                name="email"
                                                required
                                                placeholder="Digite seu email"
                                                value={formData.email}
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                            />
                                        </div>

                                        <div>
                                            <label
                                                htmlFor="mensagem"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Mensagem
                                            </label>
                                            <textarea
                                                id="mensagem"
                                                name="mensagem"
                                                rows={5}
                                                required
                                                placeholder="Digite sua mensagem"
                                                value={formData.mensagem}
                                                onChange={handleChange}
                                                className="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                            />
                                        </div>

                                        <button
                                            type="button"
                                            onClick={handleSubmit}
                                            className="w-full transform rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 px-6 py-4 font-semibold text-white shadow-md transition hover:scale-105 hover:shadow-lg"
                                        >
                                            Enviar Mensagem
                                        </button>
                                    </form>
                                </div>

                                <div className="hidden flex-1 items-center justify-center bg-gradient-to-br from-blue-100 to-purple-100 p-8 lg:flex">
                                    <div className="relative">
                                        <div className="absolute -inset-6 rounded-2xl bg-gradient-to-r from-blue-200 to-purple-200 opacity-40 blur-lg"></div>
                                        <img
                                            src="/assets/images/conheca_2.png"
                                            alt="Ilustração de contato"
                                            className="relative rounded-2xl shadow-xl"
                                        />
                                        <div className="absolute -top-4 -right-4 h-8 w-8 animate-pulse rounded-full bg-yellow-300 opacity-70"></div>
                                        <div
                                            className="absolute -bottom-4 -left-4 h-6 w-6 animate-bounce rounded-full bg-pink-300 opacity-70"
                                            style={{ animationDelay: '1s' }}
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </MarketingLayout>
    );
};

export default Home;
