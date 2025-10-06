import AppLayout from '@/layouts/AppLayout';
import { useState } from 'react';
import '../../../css/pages/hospitais.css';

const Index: React.FC = () => {
    const [cidadeSelecionada, setCidadeSelecionada] =
        useState<string>('portoAlegre');

    const handleExibriConteudo = (city: string) => {
        setCidadeSelecionada(city);
    };

    const cidades = [
        { id: 'portoAlegre', name: 'PORTO ALEGRE' },
        { id: 'santaMaria', name: 'SANTA MARIA' },
        { id: 'pelotas', name: 'PELOTAS' },
    ];

    const hospitais = {
        portoAlegre: [
            {
                id: 1,
                name: 'Hospital Santa Clara - Complexo Santa Casa',
                address:
                    'Rua Professor Annes Dias, 135 - Centro Histórico, Porto Alegre - RS',
                image: '../assets/images/hospital-santa-clara.png',
                layout: 'normal',
            },
            {
                id: 2,
                name: 'Hospital Ernesto Dornelles',
                address: 'Av. Ipiranga, 1801 - Azenha, Porto Alegre - RS',
                image: '../assets/images/hospital-ernesto-dornelli.png',
                layout: 'reverse',
            },
            {
                id: 3,
                name: 'Instituto de Cardiologia',
                address:
                    'Av. Princesa Isabel, 395 - Santana, Porto Alegre - RS',
                image: '../assets/images/instituto-de-cardiologia.png',
                layout: 'normal',
            },
            {
                id: 4,
                name: 'Ulbra Canoas',
                address: 'Av. Farroupilha, 8001 - São José, Canoas - RS',
                image: '../assets/images/ulbra-canoas.png',
                layout: 'reverse',
            },
            {
                id: 5,
                name: 'Hospital da Brigada Militar',
                address:
                    'R. Dr. Castro de Menezes, 155 - Vila Assunção, Porto Alegre - RS',
                image: '../assets/images/brigada-militar.png',
                layout: 'normal',
            },
            {
                id: 6,
                name: 'Hospital Centenário',
                address:
                    'Av. Theodomiro Porto da Fonseca, 799 - Fião, São Leopoldo - RS',
                image: '../assets/images/hospital_centenario.png',
                layout: 'reverse',
            },
        ],
        santaMaria: [
            {
                id: 1,
                name: 'Hospital Geral de Santa Maria (HGSM)',
                address:
                    "R. Mal. Hermes, 190 - Passo D'areia, Santa Maria - RS",
                image: '../assets/images/hospital-geral-santa-maria.png',
                layout: 'normal',
            },
            {
                id: 2,
                name: 'Unidade de Pronto Atendimento 24HR',
                address: 'R. Venâncio Aires, 1078 - Centro, Santa Maria - RS',
                image: '../assets/images/unimed_24h.png',
                layout: 'reverse',
            },
            {
                id: 3,
                name: 'Hospital de São Francisco',
                address:
                    'R. Joana D Arc, 465 - Nossa Sra. de Lourdes, Santa Maria - RS',
                image: '../assets/images/hospital-sao-francisco-de-assis.png',
                layout: 'normal',
            },
            {
                id: 4,
                name: 'Hospital Universitário de Santa Maria (HUSM)',
                address:
                    'Av. Roraima, 1000 Prédio 22 - Camobi, Santa Maria - RS',
                image: '../assets/images/hospital-universitario.png',
                layout: 'reverse',
            },
            {
                id: 5,
                name: 'Hospital Casa de Saúde',
                address:
                    'R. Gen. Neto, 477 - Nossa Sra. de Lourdes, Santa Maria - RS',
                image: '../assets/images/hospital-casa-de-saude.png',
                layout: 'normal',
            },
            {
                id: 6,
                name: 'Hospital Regional de Santa Maria',
                address:
                    'R. Florianópolis, 1041 - Pinheiro Machado, Santa Maria - RS',
                image: '../assets/images/hospital_regional.png',
                layout: 'reverse',
            },
            {
                id: 7,
                name: 'Hospital da Brigada Militar de Santa Maria',
                address:
                    'R. Euclídes da Cunha, 1800 - Pres. Joao Goulart, Santa Maria - RS',
                image: '../assets/images/hospital-da-brigada-sm.png',
                layout: 'normal',
            },
            {
                id: 8,
                name: 'Hospital de Caridade Alcides Brum',
                address: 'R. Floriano Peixoto, 1745 - Centro, Santa Maria - RS',
                image: '../assets/images/hospital-de-caridade.png',
                layout: 'reverse',
            },
            {
                id: 9,
                name: 'UPA 24',
                address: 'R. Ari Lagranha Domingues, 188 - Santa Maria - RS',
                image: '../assets/images/upa-sm.png',
                layout: 'normal',
            },
        ],
        pelotas: [
            {
                id: 1,
                name: 'Hospital Universitário São Francisco de Paula',
                address: 'R. Mal. Deodoro, 1123 - Centro, Pelotas - RS',
                image: '../assets/images/hospital-sao-francisco-de-paula.png',
                layout: 'reverse',
            },
            {
                id: 2,
                name: 'Hospital Escola da UFPel',
                address: 'R. Prof. Dr. Araújo, 538 - Centro, Pelotas - RS',
                image: '../assets/images/hospital-escola-ufpel.png',
                layout: 'normal',
            },
        ],
    };

    const HospitalCard = ({ hospital }: { hospital: any }) => (
        <div className="mb-16 last:mb-0">
            <div
                className={`flex flex-col items-center gap-8 lg:flex-row ${
                    hospital.layout === 'reverse' ? 'lg:flex-row-reverse' : ''
                }`}
            >
                {/* Informações do Hospital */}
                <div
                    className={`flex-1 ${
                        hospital.layout === 'reverse'
                            ? 'lg:pl-8 lg:text-right'
                            : 'lg:pr-8'
                    }`}
                >
                    <div className="rounded-2xl border border-blue-100 bg-gradient-to-r from-blue-50 to-purple-50 p-8 shadow-lg transition-all duration-300 hover:shadow-xl">
                        <h3 className="mb-4 text-2xl leading-tight font-bold text-gray-800 md:text-3xl">
                            {hospital.name}
                        </h3>
                        <p className="text-lg leading-relaxed text-gray-600 md:text-xl">
                            {hospital.address}
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
                    <div className="group relative">
                        <div className="absolute -inset-2 rounded-2xl bg-gradient-to-r from-blue-400 to-purple-400 opacity-30 blur-lg transition-opacity duration-300 group-hover:opacity-50"></div>
                        <img
                            src={hospital.image}
                            alt={hospital.name}
                            className="relative h-64 w-full rounded-xl object-cover shadow-lg transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl md:h-80"
                        />
                    </div>
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
                {cidades.map((city) => (
                    <div
                        key={city.id}
                        className={`group relative flex min-w-[220px] cursor-pointer items-center justify-between rounded-2xl border-2 border-transparent px-8 py-4 transition-all duration-500 ${
                            cidadeSelecionada === city.id
                                ? '-translate-y-2 scale-105 transform border-white/30 bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-2xl'
                                : 'bg-white/80 text-gray-800 shadow-lg backdrop-blur-sm hover:scale-105 hover:border-purple-300 hover:bg-gradient-to-r hover:from-white hover:to-purple-50 hover:shadow-xl'
                        } `}
                        onClick={() => handleExibriConteudo(city.id)}
                    >
                        {/* Efeito de brilho para o estado ativo */}
                        {cidadeSelecionada === city.id && (
                            <div className="absolute -inset-1 animate-pulse rounded-2xl bg-gradient-to-r from-purple-600 to-pink-600 opacity-30 blur"></div>
                        )}

                        <span
                            className={`relative z-10 text-lg font-bold tracking-wider uppercase ${cidadeSelecionada === city.id ? 'text-white drop-shadow-md' : 'text-gray-800 group-hover:text-purple-700'} `}
                        >
                            {city.name}
                        </span>

                        <div
                            className={`relative ml-5 text-lg transition-all duration-500 ${
                                cidadeSelecionada === city.id
                                    ? 'scale-125 rotate-180 text-yellow-300'
                                    : 'rotate-0 text-purple-500 group-hover:scale-110 group-hover:text-pink-500'
                            } `}
                        >
                            ⬆️
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
                        {Object.entries(hospitais).map(([city, hospitals]) => (
                            <div
                                key={city}
                                className={`rounded-3xl p-8 transition-all duration-500 ${
                                    cidadeSelecionada === city
                                        ? 'block scale-100 opacity-100'
                                        : 'hidden scale-95 opacity-0'
                                }`}
                            >
                                <div className="mb-12 text-center">
                                    <h2 className="mb-4 bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-4xl font-black text-transparent md:text-5xl">
                                        {city === 'portoAlegre' &&
                                            'PORTO ALEGRE'}
                                        {city === 'santaMaria' && 'SANTA MARIA'}
                                        {city === 'pelotas' && 'PELOTAS'}
                                    </h2>
                                    <div className="mx-auto h-1 w-24 rounded-full bg-gradient-to-r from-purple-400 to-blue-400"></div>
                                </div>

                                <div className="space-y-8">
                                    {hospitals.map((hospital) => (
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
