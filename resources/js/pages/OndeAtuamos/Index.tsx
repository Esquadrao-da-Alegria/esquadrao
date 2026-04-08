import AppLayout from '@/layouts/AppLayout';
import { useState } from 'react';
import '../../../css/pages/hospitais.css';
import { Hospital } from '@/types';

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

    console.log(hospitais)

    const [cidadeSelecionada, setCidadeSelecionada] =
        useState<string>('porto_alegre');

    const handleExibriConteudo = (city: string) => {
        setCidadeSelecionada(city);
    };

    const cidades = [
        { id: 'porto_alegre', name: 'PORTO ALEGRE' },
        { id: 'canoas', name: 'CANOAS' },
        { id: 'sao_leopoldo', name: 'SÃO LEOPOLDO' },
        { id: 'santa_maria', name: 'SANTA MARIA' },
        { id: 'pelotas', name: 'PELOTAS' },
    ];

    const HospitalCard = ({ hospital }: { hospital: any }) => (
        <div className="mb-16 last:mb-0">
            <div
                className={`flex flex-col items-center gap-8 lg:flex-row ${hospital.layout === 'reverse' ? 'lg:flex-row-reverse' : ''
                    }`}
            >
                {/* Informações do Hospital */}
                <div
                    className={`flex-1 ${hospital.layout === 'reverse'
                        ? 'lg:pl-8 lg:text-right'
                        : 'lg:pr-8'
                        }`}
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
                        src={hospital.url_foto}
                        alt={hospital.name}
                        className="w-full h-64 md:h-90 object-contain"
                    />
                </div>
            </div>
        </div>
    );

    return (
        <AppLayout>
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

            {/* Cidades */}
            <div className="flex flex-col items-center justify-center gap-6 rounded-3xl bg-gradient-to-br from-purple-50 to-cyan-50 p-8 shadow-2xl md:flex-row">
                {cidades.map((cidade) => (
                    <div
                        key={cidade.id}
                        className={`group relative flex min-w-[220px] cursor-pointer items-center justify-between rounded-2xl border-2 border-transparent px-8 py-4 transition-all duration-500 ${cidadeSelecionada === cidade.id
                            ? '-translate-y-2 scale-105 transform border-white/30 bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-2xl'
                            : 'bg-white/80 text-gray-800 shadow-lg backdrop-blur-sm hover:scale-105 hover:border-purple-300 hover:bg-gradient-to-r hover:from-white hover:to-purple-50 hover:shadow-xl'
                            } `}
                        onClick={() => handleExibriConteudo(cidade.id)}
                    >
                        {/* Efeito de brilho para o estado ativo */}
                        {cidadeSelecionada === cidade.id && (
                            <div className="absolute -inset-1 animate-pulse rounded-2xl bg-gradient-to-r from-purple-600 to-pink-600 opacity-30 blur"></div>
                        )}

                        <span
                            className={`relative z-10 text-lg font-bold tracking-wider uppercase ${cidadeSelecionada === cidade.id ? 'text-white drop-shadow-md' : 'text-gray-800 group-hover:text-purple-700'} `}
                        >
                            {cidade.name}
                        </span>

                        <div
                            className={`
                            relative ml-5 text-xl transition-all duration-500
                            ${cidadeSelecionada === cidade.id
                                    ? 'rotate-180 text-purple-600'
                                    : 'text-purple-500 group-hover:text-pink-500'}
                        `}
                        >
                            ▾
                        </div>

                        {/* Efeito de partículas no hover */}
                        <div className="absolute inset-0 -skew-x-12 rounded-2xl bg-gradient-to-r from-transparent via-white/10 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-100"></div>
                    </div>
                ))}
            </div>

            <div className="min-h-screen bg-gradient-to-br from-gray-50 to-blue-50 py-12">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Conteúdo das Cidades */}
                    <div className="space-y-12">
                        {Object.entries(hospitais).map(([cidadeLoop, listaAgrupada]) => (
                            <div
                                key={cidadeLoop}
                                className={`rounded-3xl p-8 transition-all duration-500 ${cidadeSelecionada === cidadeLoop
                                    ? 'block scale-100 opacity-100'
                                    : 'hidden scale-95 opacity-0'
                                    }`}
                            >
                                <div className="mb-12 text-center">
                                    <h2 className="mb-4 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-4xl font-black text-transparent md:text-5xl">
                                        {cidadeLoop === 'porto_alegre' &&
                                            'PORTO ALEGRE'}
                                        {cidadeLoop === 'santa_maria' && 'SANTA MARIA'}
                                        {cidadeLoop === 'pelotas' && 'PELOTAS'}
                                        {cidadeLoop === 'canoas' && 'CANOAS'}
                                        {cidadeLoop === 'sao_leopoldo' && 'SÃO LEOPOLDO'}
                                    </h2>
                                    <div className="mx-auto h-1 w-24 rounded-full bg-gradient-to-r from-purple-400 to-blue-400"></div>
                                </div>

                                <div className="space-y-8">
                                    {listaAgrupada.map((hospital: Hospital) => (
                                        <HospitalCard
                                            key={hospital.id}
                                            hospital={hospital}
                                        />
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default Index;
