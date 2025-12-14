import AppLayout from '@/layouts/AppLayout';
import { useState } from 'react';
import { toast } from 'react-toastify';

const Create: React.FC = () => {
    const [formData, setFormData] = useState({
        nome: '',
        descricao:'',
    });

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
    ) => {
        const target = e.target;
        const { name, value, type } = target;

        if (type === 'checkbox' && target instanceof HTMLInputElement) {
            const checked = target.checked;
            setFormData((prev) => ({
                ...prev,
                [name]: checked,
            }));
        } else {
            setFormData((prev) => ({
                ...prev,
                [name]: value,
            }));
        }
    };

    const handleSubmit = async () => {
        // Simulação de envio
        toast.success('Doutor cadastrado com sucesso!');
    };

    return (
        <AppLayout>
            <section className="mx-auto w-full max-w-6xl px-4 ">
                <div className="flex justify-center">
                    <div className="w-full max-w-4xl">
                        <div className="overflow-hidden rounded-3xl bg-gradient-to-br from-pink-50 to-blue-50 shadow-lg">
                            <div className="flex flex-col lg:flex-row">
                                {/* Formulário */}
                                <div className="flex-1 p-8 md:p-12">
                                    <h2 className="mb-8 text-3xl font-bold text-gray-900 md:text-4xl">
                                        Cadastrar Doutor
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
                                                name="nome"
                                                id="nome"
                                                required
                                                placeholder="Digite o nome do hospital"
                                                value={formData.nome}
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/* Descrição */}
                                        <div>
                                            <label
                                                htmlFor="endereco_formatado"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                Descrição
                                            </label>
                                            <input
                                               type="text"
                                                name="descrição"
                                                id="descricao"
                                                required
                                                placeholder="Digite a descrição do doutor"
                                                value={formData.descricao}
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div>

                                        {/* URL da Imagem */}
                                        {/* <div>
                                            <label
                                                htmlFor="imagem_url"
                                                className="mb-2 block text-sm font-medium text-gray-700"
                                            >
                                                URL da Imagem
                                            </label>
                                            <input
                                                type="text"
                                                name="imagem_url"
                                                id="imagem_url"
                                                placeholder="Ex: /assets/images/hospital-escola-ufpel.png"
                                                value={formData.imagem_url}
                                                onChange={handleChange}
                                                className="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 shadow-sm transition-all duration-300 focus:border-transparent focus:ring-2 focus:ring-pink-500"
                                            />
                                        </div> */}



                                        {/* Botão Salvar */}
                                        <button
                                            type="button"
                                            onClick={handleSubmit}
                                            className="w-full bg-gradient-to-r from-pink-500 to-blue-500 px-6 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
                                        >
                                            Salvar Doutor
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </AppLayout>
    );
};

export default Create;
