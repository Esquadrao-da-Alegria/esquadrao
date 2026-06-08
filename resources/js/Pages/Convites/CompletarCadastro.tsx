import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout';
import { Cidade } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import React from 'react';

interface ConviteDados {
    email: string;
    nome: string;
    telefone: string | null;
    data_nascimento: string | null;
    cidade_base_id: number | null;
}

interface Props {
    token: string;
    estado: 'valido' | 'invalido' | 'expirado' | 'utilizado' | 'cancelado';
    mensagem?: string | null;
    convite?: ConviteDados | null;
    cidades: Cidade[];
}

const somenteNumeros = (valor: string) => valor.replace(/\D/g, '').slice(0, 11);

const formatarTelefone = (valor: string) => {
    const numeros = somenteNumeros(valor);

    if (numeros.length <= 2) {
        return numeros.length > 0 ? `(${numeros}` : '';
    }

    if (numeros.length <= 6) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2)}`;
    }

    if (numeros.length <= 10) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 6)}-${numeros.slice(6)}`;
    }

    return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7)}`;
};

const dataAtualLocal = () => {
    const data = new Date();
    const ano = data.getFullYear();
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const dia = String(data.getDate()).padStart(2, '0');

    return `${ano}-${mes}-${dia}`;
};

const hoje = dataAtualLocal();

export default function CompletarCadastro({
    token,
    estado,
    mensagem,
    convite,
    cidades,
}: Props) {
    const form = useForm({
        nome_completo: convite?.nome ?? '',
        email: convite?.email ?? '',
        telefone: convite?.telefone ? formatarTelefone(convite.telefone) : '',
        data_nascimento: convite?.data_nascimento ?? '',
        cidade_base_id: convite?.cidade_base_id
            ? String(convite.cidade_base_id)
            : '',
        password: '',
        password_confirmation: '',
    });

    const conviteValido = estado === 'valido' && convite !== null;
    const errors = form.errors as typeof form.errors & {
        token?: string;
    };

    const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();

        form.transform((data) => ({
            ...data,
            telefone: formatarTelefone(data.telefone),
        }));

        form.post(`/convites/${token}/concluir`, {
            preserveScroll: true,
        });
    };

    return (
        <AuthLayout
            title={
                conviteValido
                    ? 'Complete seu cadastro'
                    : 'Convite indisponível'
            }
            description={
                conviteValido
                    ? 'Preencha seus dados para acessar o painel do Esquadrão da Alegria.'
                    : 'Não foi possível abrir este convite.'
            }
        >
            <Head title="Completar cadastro" />

            {conviteValido ? (
                <form onSubmit={handleSubmit} className="flex flex-col gap-5">
                    {errors.token ? (
                        <div className="rounded-lg border border-red-200 bg-red-50 px-3 py-2.5 text-sm text-red-800">
                            {errors.token}
                        </div>
                    ) : null}

                    <div className="space-y-2">
                        <Label htmlFor="nome_completo">Nome completo</Label>
                        <Input
                            id="nome_completo"
                            value={form.data.nome_completo}
                            onChange={(event) =>
                                form.setData(
                                    'nome_completo',
                                    event.target.value,
                                )
                            }
                            autoComplete="name"
                            required
                            autoFocus
                        />
                        <InputError message={form.errors.nome_completo} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="email">E-mail</Label>
                        <Input
                            id="email"
                            type="email"
                            value={form.data.email}
                            readOnly
                            disabled
                            className="bg-muted"
                        />
                        <InputError message={form.errors.email} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="telefone">Telefone</Label>
                        <Input
                            id="telefone"
                            type="tel"
                            value={form.data.telefone}
                            onChange={(event) =>
                                form.setData(
                                    'telefone',
                                    formatarTelefone(event.target.value),
                                )
                            }
                            autoComplete="tel"
                            inputMode="numeric"
                            maxLength={15}
                            placeholder="(73) 99999-9999"
                            required
                        />
                        <InputError message={form.errors.telefone} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="data_nascimento">
                            Data de nascimento
                        </Label>
                        <Input
                            id="data_nascimento"
                            type="date"
                            value={form.data.data_nascimento}
                            max={hoje}
                            onChange={(event) =>
                                form.setData(
                                    'data_nascimento',
                                    event.target.value,
                                )
                            }
                            required
                        />
                        <InputError message={form.errors.data_nascimento} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="cidade_base_id">Cidade base</Label>
                        <select
                            id="cidade_base_id"
                            value={form.data.cidade_base_id}
                            onChange={(event) =>
                                form.setData(
                                    'cidade_base_id',
                                    event.target.value,
                                )
                            }
                            required
                            className="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">Selecione uma cidade</option>
                            {cidades.map((cidade) => (
                                <option key={cidade.id} value={cidade.id}>
                                    {cidade.nome}
                                </option>
                            ))}
                        </select>
                        <InputError message={form.errors.cidade_base_id} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password">Senha</Label>
                        <Input
                            id="password"
                            type="password"
                            value={form.data.password}
                            onChange={(event) =>
                                form.setData('password', event.target.value)
                            }
                            autoComplete="new-password"
                            required
                        />
                        <InputError message={form.errors.password} />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password_confirmation">
                            Confirmar senha
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            value={form.data.password_confirmation}
                            onChange={(event) =>
                                form.setData(
                                    'password_confirmation',
                                    event.target.value,
                                )
                            }
                            autoComplete="new-password"
                            required
                        />
                        <InputError
                            message={form.errors.password_confirmation}
                        />
                    </div>

                    <Button
                        type="submit"
                        className="mt-2 w-full"
                        disabled={form.processing}
                    >
                        {form.processing ? (
                            <LoaderCircle
                                className="size-4 animate-spin"
                                aria-hidden
                            />
                        ) : null}
                        Concluir cadastro
                    </Button>
                </form>
            ) : (
                <div className="flex flex-col gap-5 text-center">
                    <div className="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        {mensagem ??
                            'Este convite não está disponível no momento.'}
                    </div>

                    {estado === 'utilizado' ? (
                        <TextLink href="/login" className="font-medium">
                            Ir para o login
                        </TextLink>
                    ) : null}
                </div>
            )}
        </AuthLayout>
    );
}
