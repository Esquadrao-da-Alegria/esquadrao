import { login } from '@/routes';
import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';

interface RegisterProps {
    invitation?: {
        email: string;
        invitation_token: string;
    };
    token?: string;
}

export default function Register({ invitation, token }: RegisterProps) {
    return (
        <AuthLayout
            title="Crie sua conta"
            description="Insire os dados abaixo para criar a sua conta"
        >
            <Head title="Registrar-se" />

            <Form
                method="post"
                action="/register"
                resetOnSuccess={['password', 'password_confirmation']}
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        {/* Campo oculto para o token do convite */}
                        {token && (
                            <input type="hidden" name="invitation_token" value={token} />
                        )}

                        {/* Campos do formulário */}
                        <div className="grid gap-5">
                            {/* Nome */}
                            <div className="grid gap-2">
                                <Label htmlFor="name">Nome</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="name"
                                    name="name"
                                    placeholder="Nome completo"
                                    className="rounded-xl border border-gray-200 px-4 py-3 shadow-sm focus:border-transparent focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                                />
                                <InputError message={errors.name} className="mt-1" />
                            </div>

                            {/* Email */}
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    tabIndex={2}
                                    autoComplete="email"
                                    name="email"
                                    placeholder="email@exemplo.com"
                                    value={invitation?.email || ''}
                                    readOnly={!!invitation?.email}
                                    className="rounded-xl border border-gray-200 px-4 py-3 shadow-sm focus:border-transparent focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                                />
                                <InputError message={errors.email} className="mt-1" />
                            </div>

                            {/* Password */}
                            <div className="grid gap-2">
                                <Label htmlFor="password">Senha</Label>
                                <Input
                                    id="password"
                                    type="password"
                                    required
                                    tabIndex={3}
                                    autoComplete="new-password"
                                    name="password"
                                    placeholder="Senha"
                                    className="rounded-xl border border-gray-200 px-4 py-3 shadow-sm focus:border-transparent focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                                />
                                <InputError message={errors.password} className="mt-1" />
                            </div>

                            {/* Confirm Password */}
                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">Confirme sua senha</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    required
                                    tabIndex={4}
                                    autoComplete="new-password"
                                    name="password_confirmation"
                                    placeholder="Confirme sua senha"
                                    className="rounded-xl border border-gray-200 px-4 py-3 shadow-sm focus:border-transparent focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                                />
                                <InputError message={errors.password_confirmation} className="mt-1" />
                            </div>

                            {/* Profile Visibility */}
                            <div className="grid gap-2">
                                <Label htmlFor="profile_visibility">Visibilidade do Perfil</Label>
                                <div className="space-y-3">
                                    <div className="flex items-start space-x-3 p-4 border border-gray-200 rounded-xl hover:border-blue-300 transition-colors">
                                        <input
                                            type="radio"
                                            id="profile_public"
                                            name="profile_visibility"
                                            value="public"
                                            defaultChecked
                                            className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <div className="flex-1">
                                            <label htmlFor="profile_public" className="block text-sm font-medium text-gray-900 cursor-pointer">
                                                Perfil Público
                                            </label>
                                            <p className="text-xs text-gray-500 mt-1">
                                                Seu perfil será visível na listagem de voluntários
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-start space-x-3 p-4 border border-gray-200 rounded-xl hover:border-blue-300 transition-colors">
                                        <input
                                            type="radio"
                                            id="profile_private"
                                            name="profile_visibility"
                                            value="private"
                                            className="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
                                        />
                                        <div className="flex-1">
                                            <label htmlFor="profile_private" className="block text-sm font-medium text-gray-900 cursor-pointer">
                                                Perfil Privado
                                            </label>
                                            <p className="text-xs text-gray-500 mt-1">
                                                Seu perfil será privado e não aparecerá na listagem
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <InputError message={errors.profile_visibility} className="mt-1" />
                            </div>

                            {/* Botão */}
                            <Button
                                type="submit"
                                className="w-full transform bg-gradient-to-r from-blue-500 to-purple-500 px-6 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl flex items-center justify-center gap-2"
                                tabIndex={6}
                                data-test="register-user-button"
                            >
                                {processing && (
                                    <LoaderCircle className="h-5 w-5 animate-spin" />
                                )}
                                Criar conta
                            </Button>
                        </div>

                        {/* Link para login */}
                        <div className="mt-6 text-center text-sm text-gray-500">
                            Já tem uma conta?{' '}
                            <TextLink href={login()} tabIndex={7}>
                                Entrar
                            </TextLink>
                        </div>

                        {/* Link para solicitar convite se não tiver token */}
                        {!token && (
                            <div className="mt-2 text-center text-sm text-gray-500">
                                Precisa de um convite?{' '}
                                <TextLink href="/request-invitation" tabIndex={8}>
                                    Solicitar Convite
                                </TextLink>
                            </div>
                        )}

                        {/* Elementos decorativos animados */}
                        <div className="absolute -top-8 -left-8 h-12 w-12 animate-pulse rounded-full bg-yellow-300 opacity-40"></div>
                        <div className="absolute -bottom-8 -right-8 h-8 w-8 animate-bounce rounded-full bg-pink-300 opacity-30" style={{ animationDelay: '0.5s' }}></div>
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
