import PasswordController from '@/actions/App/Http/Controllers/Settings/PasswordController';
import InputError from '@/components/input-error';
import PainelLayout from '@/layouts/PainelLayout';
import SettingsLayout from '@/layouts/Settings/Layout';
import { Transition } from '@headlessui/react';
import { Form, Head } from '@inertiajs/react';
import { useRef } from 'react';

import HeadingSmall from '@/components/heading-small';
import { painelInputClass, painelLabelClass } from '@/lib/painelFormFieldClasses';
import { Check } from 'lucide-react';

export default function Password() {
    const passwordInput = useRef<HTMLInputElement>(null);
    const currentPasswordInput = useRef<HTMLInputElement>(null);

    return (
        <PainelLayout>
            <Head title="Senha" />

            <div className="mx-auto max-w-7xl px-6 pb-16">
                <SettingsLayout>
                    <div className="space-y-6">
                        <HeadingSmall
                            title="Atualizar senha"
                            description="Use uma senha longa e aleatória para manter sua conta segura"
                        />

                        <Form
                            {...PasswordController.update.form()}
                            options={{
                                preserveScroll: true,
                            }}
                            resetOnError={[
                                'password',
                                'password_confirmation',
                                'current_password',
                            ]}
                            resetOnSuccess
                            onError={(errors) => {
                                if (errors.password) {
                                    passwordInput.current?.focus();
                                }

                                if (errors.current_password) {
                                    currentPasswordInput.current?.focus();
                                }
                            }}
                            className="space-y-6"
                        >
                            {({ errors, processing, recentlySuccessful }) => (
                                <>
                                    <div>
                                        <label
                                            htmlFor="current_password"
                                            className={painelLabelClass}
                                        >
                                            Senha atual
                                        </label>

                                        <input
                                            id="current_password"
                                            ref={currentPasswordInput}
                                            name="current_password"
                                            type="password"
                                            className={painelInputClass}
                                            autoComplete="current-password"
                                            placeholder="Senha atual"
                                        />

                                        <InputError
                                            className="mt-2"
                                            message={errors.current_password}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="password"
                                            className={painelLabelClass}
                                        >
                                            Nova senha
                                        </label>

                                        <input
                                            id="password"
                                            ref={passwordInput}
                                            name="password"
                                            type="password"
                                            className={painelInputClass}
                                            autoComplete="new-password"
                                            placeholder="Nova senha"
                                        />

                                        <InputError
                                            className="mt-2"
                                            message={errors.password}
                                        />
                                    </div>

                                    <div>
                                        <label
                                            htmlFor="password_confirmation"
                                            className={painelLabelClass}
                                        >
                                            Confirmar senha
                                        </label>

                                        <input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            className={painelInputClass}
                                            autoComplete="new-password"
                                            placeholder="Confirmar senha"
                                        />

                                        <InputError
                                            className="mt-2"
                                            message={
                                                errors.password_confirmation
                                            }
                                        />
                                    </div>

                                    <div className="flex flex-wrap items-center gap-4">
                                        <button
                                            type="submit"
                                            disabled={processing}
                                            data-test="update-password-button"
                                            className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"
                                        >
                                            <Check
                                                className="size-4"
                                                aria-hidden
                                            />
                                            {processing
                                                ? 'Salvando...'
                                                : 'Salvar senha'}
                                        </button>

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
                </SettingsLayout>
            </div>
        </PainelLayout>
    );
}
