import MarketingLayout from '@/layouts/MarketingLayout';
import { useState } from 'react';
import { toast } from 'react-toastify';

const Index: React.FC = () => {
    const [formData, setFormData] = useState({
        nome: '',
        email: '',
        mensagem: '',
        honeypot: '',
    });
    const [enviando, setEnviando] = useState(false);

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

    const resetarFormulario = () => {
        setFormData({
            nome: '',
            email: '',
            mensagem: '',
            honeypot: '',
        });
    };

    const handleSubmit = async () => {
        // Proteção Anti-Bot (Honeypot): se preenchido, ignora silenciosamente
        if (formData.honeypot) {
            toast.success('Mensagem enviada com sucesso!');
            resetarFormulario();
            return;
        }

        if (!formData.nome || !formData.email || !formData.mensagem) {
            toast.error('Por favor, preencha todos os campos.');
            return;
        }

        setEnviando(true);
        try {
            const url = 'https://api.staticforms.xyz/submit';
            const dadosPost = { ...formData, accessKey };

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
        } finally {
            setEnviando(false);
        }
    };

    return (
        <MarketingLayout>
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
                                        {/* Campo Honeypot (Armadilha Anti-Bot) */}
                                        <div className="hidden" aria-hidden="true">
                                            <input
                                                type="text"
                                                name="honeypot"
                                                tabIndex={-1}
                                                autoComplete="off"
                                                value={formData.honeypot}
                                                onChange={handleChange}
                                            />
                                        </div>

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
                                                name="nome"
                                                id="nome"
                                                required
                                                placeholder="Digite seu nome"
                                                value={formData.nome}
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
                                                name="mensagem"
                                                id="mensagem"
                                                rows={5}
                                                required
                                                placeholder="Digite sua mensagem"
                                                value={formData.mensagem}
                                                onChange={handleChange}
                                                className="w-full resize-none rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-300 focus:border-transparent focus:ring-2 focus:ring-blue-500"
                                            />
                                        </div>

                                        {/* Botão Enviar */}
                                        <button
                                            type="button"
                                            onClick={handleSubmit}
                                            disabled={enviando}
                                            className="w-full transform rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 px-6 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            {enviando ? 'Enviando...' : 'Enviar Mensagem'}
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
                                            className="relative w-full h-full rounded-2xl shadow-lg"
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
        </MarketingLayout>
    );
};

export default Index;
