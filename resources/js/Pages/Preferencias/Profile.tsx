import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { send } from '@/routes/verification';
import { type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Form, Head, Link, usePage } from '@inertiajs/react';

import DeleteUser from '@/components/delete-user';
import BotaoSalvar from '@/components/Painel/Forms/BotaoSalvar/Show';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { painelInputClass, painelLabelClass } from '@/lib/painelFormFieldClasses';
import PainelLayout from '@/layouts/PainelLayout';
import SettingsLayout from '@/layouts/Settings/Layout';

export default function Profile({
    mustVerifyEmail,
    status,
}: {
    mustVerifyEmail: boolean;
    status?: string;
}) {
    const { auth } = usePage<SharedData>().props;

    return (
        <PainelLayout>
            <Head title="Configurações do perfil" />

            <div className="mx-auto max-w-7xl px-6 pb-16">
                <SettingsLayout>
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Informações do perfil"
                            description="Atualize seu nome e endereço de e-mail"
                        />

                        <Form
                            {...ProfileController.update.form()}
                            options={{
                                preserveScroll: true,
                            }}
                            className="space-y-6"
                        >
                            {({ processing, recentlySuccessful, errors }) => (
                                <>
                                    <div>
                                        <label
                                            htmlFor="name"
                                            className={painelLabelClass}
                                        >
                                            Nome
                                        </label>

                                        <input
                                            id="name"
                                            className={painelInputClass}
                                            defaultValue={auth.user.name}
                                            name="name"
                                            required
                                            autoComplete="name"
                                            placeholder="Nome completo"
                                        />

                                        <InputError
                                            className="mt-2"
                                            message={errors.name}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="email"
                                            className={painelLabelClass}
                                        >
                                            E-mail
                                        </label>

                                        <input
                                            id="email"
                                            type="email"
                                            className={painelInputClass}
                                            defaultValue={auth.user.email}
                                            name="email"
                                            required
                                            autoComplete="username"
                                            placeholder="E-mail"
                                        />

                                        <InputError
                                            className="mt-2"
                                            message={errors.email}
                                        />
                                    </div>

                                    {mustVerifyEmail &&
                                        auth.user.email_verified_at ===
                                            null && (
                                            <div>
                                                <p className="-mt-4 text-sm text-amber-900/60">
                                                    Seu endereço de e-mail não
                                                    foi verificado.{' '}
                                                    <Link
                                                        href={send()}
                                                        as="button"
                                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                                    >
                                                        Clique aqui para reenviar
                                                        o e-mail de verificação.
                                                    </Link>
                                                </p>

                                                {status ===
                                                    'verification-link-sent' && (
                                                    <div className="mt-2 text-sm font-medium text-green-600">
                                                        Um novo link de
                                                        verificação foi enviado
                                                        para o seu e-mail.
                                                    </div>
                                                )}
                                            </div>
                                        )}

                                    <div className="flex flex-wrap items-center gap-4">
                                        <BotaoSalvar
                                            type="submit"
                                            disabled={processing}
                                            salvando={processing}
                                            data-test="update-profile-button"
                                        />

                                        <Transition
                                            show={recentlySuccessful}
                                            enter="transition ease-in-out"
                                            enterFrom="opacity-0"
                                            leave="transition ease-in-out"
                                            leaveTo="opacity-0"
                                        >
                                            <p className="text-sm text-neutral-600">
                                                Salvo
                                            </p>
                                        </Transition>
                                    </div>
                                </>
                            )}
                        </Form>
                    </div>

                    <DeleteUser />
                </SettingsLayout>
            </div>
        </PainelLayout>
    );
}
