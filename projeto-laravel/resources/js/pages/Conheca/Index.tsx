import AppLayout from '@/layouts/AppLayout';

const Index: React.FC = () => {
    return (
        <AppLayout>
            <section className="relative bg-white py-12 md:py-20">
                <div className="container mx-auto flex flex-col items-center px-4 md:flex-row">
                    {/* Bandeirinhas decorativas */}
                    <img
                        src="../assets/img/bandeiras-banner.png"
                        alt="Bandeirinhas"
                        className="absolute top-0 left-1/2 w-3/4 -translate-x-1/2 md:w-2/3"
                    />

                    {/* Texto à esquerda */}
                    <div className="mt-12 flex w-full justify-center text-center md:mt-0 md:w-1/2 md:justify-start md:text-left">
                        <div>
                            <h1 className="text-4xl font-bold tracking-widest text-orange-600 md:text-5xl">
                                HOSPITAIS
                            </h1>
                            <h3 className="mt-3 text-lg text-gray-700 md:text-2xl">
                                CIDADES EM QUE ATUAMOS
                            </h3>
                        </div>
                    </div>

                    {/* Imagem à direita */}
                    <div className="mt-10 flex w-full justify-center md:mt-0 md:w-1/2">
                        <img
                            src="../assets/img/imagem_palhacos.png"
                            alt="Palhaços no hospital"
                            className="max-w-full md:max-w-md"
                        />
                    </div>
                </div>
            </section>
        </AppLayout>
    );
};

export default Index;
