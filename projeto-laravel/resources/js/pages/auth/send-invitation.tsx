import { Form, Head } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';

import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';


interface SendInvitationProps {
    status?: string;
}

export default function SendInvitation({ status }: SendInvitationProps) {
    return (
        <AuthLayout
            title="Enviar convite"
            description="Informe o email de um voluntário para enviar um convite de registro"
        >
            <Head title="Enviar convite" />

            <Form
                method="post"
                action="/send-invitation"
                className="flex flex-col gap-6"
            >
                {({ processing, errors }) => (
                    <>
                        <div className="grid gap-5">
                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    autoFocus
                                    tabIndex={1}
                                    autoComplete="email"
                                    placeholder="seu@email.com"
                                    className="rounded-xl border border-gray-200 px-4 py-3 shadow-sm focus:border-transparent focus:ring-2 focus:ring-blue-500 transition-all duration-300"
                                />
                                <InputError message={errors.email} className="mt-1" />
                            </div>

                            <Button
                                type="submit"
                                className="w-full transform bg-gradient-to-r from-blue-500 to-purple-500 px-6 py-4 font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl flex items-center justify-center gap-2"
                                tabIndex={2}
                                disabled={processing}
                            >
                                {processing && (
                                    <LoaderCircle className="h-5 w-5 animate-spin" />
                                )}
                                Solicitar Convite
                            </Button>
                        </div>

                        <div className="mt-6 text-center text-sm text-gray-500">
                            Já tem uma conta?{' '}
                            <TextLink href="/login" tabIndex={3}>
                                Entrar
                            </TextLink>
                        </div>

                        {status && (
                            <div className="mb-4 text-center text-sm font-medium text-green-600">
                                {status}
                            </div>
                        )}
                    </>
                )}
            </Form>
        </AuthLayout>
    );
}
