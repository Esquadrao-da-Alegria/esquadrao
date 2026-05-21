import { painelInputClass, painelLabelClass } from '@/lib/painelFormFieldClasses'
import { Cargo } from '@/types'
import React from 'react'

export interface VoluntarioFormValues {
    name: string
    email: string
    password: string
    password_confirmation: string
    cargo_ids: number[]
}

interface Props {
    data: VoluntarioFormValues
    errors: Partial<Record<keyof VoluntarioFormValues | 'cargo_ids' | 'cargo_ids.0', string>> &
        Record<string, string | undefined>
    cargos: Cargo[]
    mode: 'create' | 'edit'
    onFieldChange: <K extends keyof VoluntarioFormValues>(
        campo: K,
        valor: VoluntarioFormValues[K],
    ) => void
}

const VoluntarioFormShow: React.FC<Props> = ({
    data,
    errors,
    cargos,
    mode,
    onFieldChange,
}) => {
    const toggleCargo = (cargoId: number) => {
        const set = new Set(data.cargo_ids)
        if (set.has(cargoId)) {
            set.delete(cargoId)
        } else {
            set.add(cargoId)
        }
        onFieldChange('cargo_ids', Array.from(set))
    }

    return (
        <div className="space-y-6">
            <div>
                <label htmlFor="voluntario_name" className={painelLabelClass}>
                    Nome *
                </label>
                <input
                    type="text"
                    name="name"
                    id="voluntario_name"
                    required
                    autoComplete="name"
                    placeholder="Nome completo"
                    value={data.name}
                    onChange={(e) => onFieldChange('name', e.target.value)}
                    className={painelInputClass}
                />
                {errors.name ? (
                    <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                ) : null}
            </div>

            <div>
                <label htmlFor="voluntario_email" className={painelLabelClass}>
                    E-mail *
                </label>
                <input
                    type="email"
                    name="email"
                    id="voluntario_email"
                    required
                    autoComplete="email"
                    placeholder="email@exemplo.com"
                    value={data.email}
                    onChange={(e) => onFieldChange('email', e.target.value)}
                    className={painelInputClass}
                />
                {errors.email ? (
                    <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                ) : null}
            </div>

            {mode === 'create' ? (
                <>
                    <div>
                        <label htmlFor="voluntario_password" className={painelLabelClass}>
                            Senha *
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="voluntario_password"
                            required
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => onFieldChange('password', e.target.value)}
                            className={painelInputClass}
                        />
                        {errors.password ? (
                            <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                        ) : null}
                    </div>
                    <div>
                        <label htmlFor="voluntario_password_confirmation" className={painelLabelClass}>
                            Confirmar senha *
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="voluntario_password_confirmation"
                            required
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) =>
                                onFieldChange('password_confirmation', e.target.value)
                            }
                            className={painelInputClass}
                        />
                    </div>
                </>
            ) : (
                <>
                    <div>
                        <label htmlFor="voluntario_password_edit" className={painelLabelClass}>
                            Nova senha (opcional)
                        </label>
                        <input
                            type="password"
                            name="password"
                            id="voluntario_password_edit"
                            autoComplete="new-password"
                            value={data.password}
                            onChange={(e) => onFieldChange('password', e.target.value)}
                            className={painelInputClass}
                        />
                        {errors.password ? (
                            <p className="mt-1 text-sm text-red-600">{errors.password}</p>
                        ) : null}
                    </div>
                    <div>
                        <label
                            htmlFor="voluntario_password_confirmation_edit"
                            className={painelLabelClass}
                        >
                            Confirmar nova senha
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            id="voluntario_password_confirmation_edit"
                            autoComplete="new-password"
                            value={data.password_confirmation}
                            onChange={(e) =>
                                onFieldChange('password_confirmation', e.target.value)
                            }
                            className={painelInputClass}
                        />
                    </div>
                </>
            )}

            <fieldset>
                <legend className={`${painelLabelClass} mb-3`}>Cargos * (um ou mais)</legend>
                <p className="mb-3 text-sm text-amber-900/60">
                    Marque todas as funções deste voluntário na organização.
                </p>
                <ul className="max-h-64 space-y-2 overflow-y-auto rounded-xl border border-amber-100 bg-amber-50/40 p-3">
                    {cargos.map((cargo) => (
                        <li key={cargo.id}>
                            <label className="flex cursor-pointer items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-white/80">
                                <input
                                    type="checkbox"
                                    className="h-4 w-4 rounded border-amber-300 text-amber-600 focus:ring-amber-500"
                                    checked={data.cargo_ids.includes(cargo.id)}
                                    onChange={() => toggleCargo(cargo.id)}
                                />
                                <span className="text-sm font-medium text-amber-950">
                                    {cargo.nome}
                                </span>
                            </label>
                        </li>
                    ))}
                </ul>
                {errors.cargo_ids ? (
                    <p className="mt-1 text-sm text-red-600">{errors.cargo_ids}</p>
                ) : null}
            </fieldset>
        </div>
    )
}

export default VoluntarioFormShow
