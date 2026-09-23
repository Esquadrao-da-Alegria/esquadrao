import SiteLayout from '@/layouts/SiteLayout';
import { Hospital } from '@/types';
import { useState } from 'react';

interface ListaHospitais {
    porto_alegre: Hospital[];
    santa_maria: Hospital[];
    pelotas: Hospital[];
    sao_leopoldo: Hospital[];
    canoas: Hospital[];
}

interface Props {
    hospitais: ListaHospitais;
}

const Index: React.FC<Props> = ({ hospitais }) => {
    console.log(hospitais);

    const [cidadeSelecionada, setCidadeSelecionada] =
        useState<keyof ListaHospitais>('porto_alegre');

    const handleExibriConteudo = (city: keyof ListaHospitais) => {
        setCidadeSelecionada(city);
    };

    const cidades: { id: keyof ListaHospitais; name: string }[] = [
        { id: 'porto_alegre', name: 'PORTO ALEGRE' },
        { id: 'canoas', name: 'CANOAS' },
        { id: 'sao_leopoldo', name: 'SÃO LEOPOLDO' },
        { id: 'santa_maria', name: 'SANTA MARIA' },
        { id: 'pelotas', name: 'PELOTAS' },
    ];

    const HospitalCard = ({ hospital }: { hospital: Hospital }) => (
        <div className="mb-16 last:mb-0">
            <div
                className="flex flex-col items-center gap-8 lg:flex-row"
            >
                {/* Informações do Hospital */}
                <div
                    className="flex-1 lg:pr-8"
                >
                    <div className="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-purple-50 p-8 shadow-lg transition-all duration-300 hover:shadow-xl">
                        <h3 className="mb-4 text-2xl leading-tight font-bold text-gray-800 md:text-3xl">
                            {hospital.nome}
                        </h3>
                        <p className="text-lg leading-relaxed text-gray-600 md:text-xl">
                            {hospital.endereco}
                        </p>
                        <div className="mt-6 flex items-center gap-3">
                            <div className="h-3 w-3 animate-pulse rounded-full bg-green-400"></div>
                            <span className="font-semibold text-green-600">
                                Ativo
                            </span>
                        </div>
                    </div>
                </div>

                {/* Imagem do Hospital */}
                <div className="flex-1">
                    <img
                        src={hospital.url_foto ?? undefined}
                        alt={hospital.nome}
                        className="h-64 w-full object-contain md:h-90"
                    />
                </div>
            </div>
        </div>
    );

    return (
        <SiteLayout>
            {/* Banner Principal */}
            <section className="animate-fadeIn relative w-full overflow-hidden bg-gradient-to-b from-pink-50 via-purple-50 to-blue-50 py-20 md:py-12">
                {/* Elemento decorativo animado no topo */}
                <div className="absolute top-0 right-0 left-0 h-2 animate-pulse bg-gradient-to-r from-pink-400 via-purple-400 to-blue-400"></div>

                {/* Bolhas decorativas animadas */}
                <div className="absolute top-6 left-12 h-6 w-6 animate-bounce rounded-full bg-pink-300 opacity-70"></div>
                <div
                    className="absolute top-10 right-24 h-5 w-5 animate-bounce rounded-full bg-purple-300 opacity-70"
                    style={{ animationDelay: '0.2s' }}
                ></div>
                <div
                    className="absolute top-14 left-1/3 h-6 w-6 animate-bounce rounded-full bg-blue-300 opacity-70"
                    style={{ animationDelay: '0.5s' }}
                ></div>
                <div
                    className="absolute right-1/4 bottom-10 h-4 w-4 animate-bounce rounded-full bg-yellow-300 opacity-70"
                    style={{ animationDelay: '0.7s' }}
                ></div>

                <div className="mx-auto max-w-7xl px-4">
                    <div className="flex flex-col items-center gap-8 lg:flex-row lg:items-center lg:gap-16">
                        {/* Texto */}
                        <div className="flex flex-col items-center text-center lg:w-2/5 lg:items-start lg:text-left">
                            <h1
                                className="animate-fadeInUp mb-6 bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 bg-clip-text text-4xl font-extrabold text-transparent md:text-5xl lg:text-6xl"
                                style={{ fontFamily: "'Fredoka One', cursive" }}
                            >
                                Hospitais em que atuamos
                            </h1>

                            <div className="mb-8 h-1 w-28 animate-pulse rounded-full bg-gradient-to-r from-pink-400 via-purple-400 to-blue-400"></div>
                            <p className="animate-fadeInUp max-w-md text-lg leading-relaxed font-medium text-gray-700 delay-200 md:text-xl">
                                Transformamos cada visita em um momento de
                                alegria e carinho pelos hospitais que passamos!
                            </p>
                        </div>

                        {/* Imagem dos palhaços */}
                        <div className="flex justify-center lg:w-3/5 lg:justify-end">
                            <div className="relative">
                                {/* Efeito de brilho sutil */}
                                <div className="animate-pulseSlow absolute -inset-4 rounded-2xl bg-gradient-to-r from-pink-100 via-purple-100 to-blue-100 opacity-50 blur-2xl"></div>
                                <img
                                    src="../assets/images/imagem_palhacos.png"
                                    alt="Palhaços no hospital"
                                    className="animate-fadeIn relative w-full max-w-2xl transform rounded-2xl shadow-2xl transition-transform delay-300 duration-500 hover:scale-110"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <nav
                aria-label="Cidades"
                className="border-b border-gray-200 bg-white"
            >
                <div className="mx-auto flex max-w-7xl flex-wrap justify-center gap-x-8 gap-y-1 px-4">
                    {cidades.map((cidade) => (
                        <button
                            key={cidade.id}
                            type="button"
                            onClick={() => handleExibriConteudo(cidade.id)}
                            className={`border-b-2 py-4 text-sm font-medium tracking-wide transition-colors ${
                                cidadeSelecionada === cidade.id
                                    ? 'border-purple-600 text-purple-700'
                                    : 'border-transparent text-gray-500 hover:text-gray-800'
                            }`}
                        >
                            {cidade.name}
                        </button>
                    ))}
                </div>
            </nav>

            <div className="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div
                        key={cidadeSelecionada}
                        className="animate-in fade-in slide-in-from-bottom-3 duration-500 rounded-3xl p-8"
                    >
                        <div className="mb-12 text-center">
                            <h2 className="mb-4 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-4xl font-black text-transparent md:text-5xl">
                                {
                                    cidades.find(
                                        (cidade) =>
                                            cidade.id === cidadeSelecionada,
                                    )?.name
                                }
                            </h2>
                            <div className="mx-auto h-1 w-24 rounded-full bg-gradient-to-r from-purple-400 to-blue-400"></div>
                        </div>

                        <div className="space-y-8">
                            {(hospitais[cidadeSelecionada] ?? []).map(
                                (hospital) => (
                                    <HospitalCard
                                        key={hospital.id}
                                        hospital={hospital}
                                    />
                                ),
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </SiteLayout>
    );
};

export default Index;
