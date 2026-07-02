import MarketingLayout from '@/layouts/MarketingLayout';
import { toastSucesso } from '@/lib/utils/toast';
import { toast } from 'react-toastify';

const Index: React.FC = () => {
    const pixCode = 'SEU_CODIGO_PIX_AQUI'; // Substitua pelo código PIX real

    const copyToClipboard = () => {
        navigator.clipboard.writeText(pixCode);
        toastSucesso("Código PIX copiado para a área de transferência");
    };

    return (
        <MarketingLayout>
            <section className="w-full bg-gradient-to-br from-pink-50 to-red-50 py-16 md:py-24">
                <div className="mx-auto max-w-6xl px-4">
                    <div className="flex flex-col items-center gap-12 lg:flex-row">
                        {/* Imagem */}
                        <div className="flex flex-1 justify-center">
                            <div className="relative">
                                <div className="absolute -inset-6 rounded-3xl bg-gradient-to-r from-pink-200 to-red-200 opacity-50 blur-xl"></div>
                                <img
                                    src="../assets/images/coracao_medico.png"
                                    alt="Mãos com coração"
                                    className="relative w-full max-w-sm rounded-2xl shadow-2xl"
                                />
                            </div>
                        </div>

                        {/* Texto e QR Code */}
                        <div className="flex-1 text-center lg:text-left">
                            {/* Texto motivacional */}
                            <p className="mb-8 text-2xl leading-relaxed font-bold text-gray-800 md:text-3xl">
                                <strong>
                                    Juntos podemos fazer o impossível acontecer:
                                </strong>
                                <span className="text-red-500">
                                    {' '}
                                    um sorriso
                                </span>{' '}
                                em cada rosto,
                                <br />
                                <span className="text-red-500">
                                    uma alegria
                                </span>{' '}
                                em cada coração.
                            </p>

                            {/* QR Code e PIX */}
                            <div className="flex flex-col items-center justify-center gap-6 md:flex-row lg:justify-start">
                                {/* QR Code */}
                                <div className="rounded-2xl bg-white p-4 shadow-lg">
                                    <img
                                        src="../assets/images/QRcode.png"
                                        alt="QR Code para doação"
                                        className="h-48 w-48 rounded-lg"
                                    />
                                </div>

                                {/* Informações PIX */}
                                <div className="text-center md:text-left">
                                    <p className="mb-2 text-lg font-semibold text-gray-700">
                                        Doe via o QRCode ao lado
                                    </p>

                                    <p className="mb-4 text-gray-500">ou</p>

                                    <div className="rounded-xl border border-red-100 bg-white p-4 shadow-lg">
                                        <p className="mb-2 text-lg font-semibold text-gray-700">
                                            Copiar PIX:
                                        </p>
                                        <button
                                            onClick={copyToClipboard}
                                            className="mx-auto flex items-center gap-2 rounded-lg bg-red-500 px-4 py-2 text-white transition-colors duration-300 hover:bg-red-600 md:mx-0"
                                        >
                                            <span>📋</span>
                                            <span className="font-semibold">
                                                Copia e Cola
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Elementos decorativos */}
                    <div className="absolute top-1/4 left-10 h-8 w-8 animate-pulse rounded-full bg-red-300 opacity-40"></div>
                    <div
                        className="absolute right-20 bottom-1/4 h-6 w-6 animate-bounce rounded-full bg-pink-300 opacity-50"
                        style={{ animationDelay: '1s' }}
                    ></div>
                </div>
            </section>
        </MarketingLayout>
    );
};

export default Index;
