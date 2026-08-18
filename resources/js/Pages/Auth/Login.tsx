import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
}

export default function Login({ status, canResetPassword }: LoginProps) {
    return (
        <AuthLayout
            title="Entre na sua conta"
            description="Use seu email e senha para acessar o painel."
        >
            <Head title="Entrar" />

            <div className="flex flex-col gap-8">
                <Form
                    {...AuthenticatedSessionController.store.form()}
                    resetOnSuccess={['password']}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="flex flex-col gap-5">
                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="email"
                                        placeholder="voce@exemplo.com"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center justify-between gap-3">
                                        <Label htmlFor="password">Senha</Label>
                                        {canResetPassword && (
                                            <TextLink
                                                href={request()}
                                                className="shrink-0 text-sm font-medium"
                                                tabIndex={5}
                                            >
                                                Esqueceu a senha?
                                            </TextLink>
                                        )}
                                    </div>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        tabIndex={2}
                                        autoComplete="current-password"
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="flex items-center gap-2.5 pt-0.5">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        tabIndex={3}
                                    />
                                    <Label
                                        htmlFor="remember"
                                        className="cursor-pointer text-sm leading-none font-normal text-muted-foreground"
                                    >
                                        Manter conectado
                                    </Label>
                                </div>
                            </div>

                            <Button
                                type="submit"
                                className="w-full"
                                tabIndex={4}
                                disabled={processing}
                                data-test="login-button"
                            >
                                {processing && (
                                    <LoaderCircle
                                        className="size-4 animate-spin"
                                        aria-hidden
                                    />
                                )}
                                Entrar
                            </Button>

                            <p className="text-center text-sm text-muted-foreground">
                                Não tem conta?{' '}
                                <TextLink
                                    href={register()}
                                    tabIndex={5}
                                    className="font-medium"
                                >
                                    Criar conta
                                </TextLink>
                            </p>
                        </>
                    )}
                </Form>

                {status && (
                    <div
                        className="rounded-lg border border-emerald-200/80 bg-emerald-50 px-3 py-2.5 text-center text-sm text-emerald-900"
                        role="status"
                    >
                        {status}
                    </div>
                )}
            </div>
        </AuthLayout>
    );
}
