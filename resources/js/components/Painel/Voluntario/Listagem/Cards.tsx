import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { edit } from '@/routes/voluntarios'
import { Link } from '@inertiajs/react'
import { Ban, Calendar, Mail, MailPlus, MapPin, MoreHorizontal, Pencil } from 'lucide-react'
import {
    formatarData,
    getCidade,
    getIniciais,
    getStatusVoluntario,
    podeCancelarConvite,
    podeReenviarConvite,
    statusMap,
} from './status'
import { AbaKey, VoluntarioListagem } from './types'

interface Props {
    aba: AbaKey
    voluntarios: VoluntarioListagem[]
    ehAdministrador: boolean
    onInativar: (voluntario: VoluntarioListagem) => void
    onReenviarConvite: (voluntario: VoluntarioListagem) => void
    onCancelarConvite: (voluntario: VoluntarioListagem) => void
}

const Cards: React.FC<Props> = ({
    aba,
    voluntarios,
    ehAdministrador,
    onInativar,
    onReenviarConvite,
    onCancelarConvite,
}) => {
    const convidados = aba === 'convidados'

    if (voluntarios.length === 0) {
        return (
            <div className="mt-6 flex flex-col items-center justify-center rounded-2xl border border-dashed border-amber-200 bg-white px-6 py-20 text-center">
                <p className="text-sm text-amber-900/50">
                    Nenhum registro encontrado.
                </p>
            </div>
        )
    }

    return (
        <ul className="mt-6 flex w-full flex-col gap-5">
            {voluntarios.map((voluntario) => {
                const statusConvite = statusMap.get(voluntario.statusKey)
                const statusVoluntario = getStatusVoluntario(voluntario)

                return (
                    <li key={voluntario.id} className="w-full">
                        <article className="w-full overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm transition duration-300 hover:border-amber-200 hover:shadow-md">
                            <div className="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:gap-6 sm:p-6">
                                <div className="shrink-0">
                                    <div className="flex size-[4.5rem] items-center justify-center rounded-2xl bg-amber-50 text-base font-semibold text-amber-900 ring-1 ring-amber-100">
                                        {getIniciais(voluntario.name)}
                                    </div>
                                </div>

                                <div className="min-w-0 flex-1">
                                    <h2 className="text-base font-semibold leading-snug text-amber-950 sm:text-lg">
                                        {voluntario.name}
                                    </h2>
                                    <p className="mt-1.5 flex gap-1.5 text-sm leading-relaxed text-amber-900/50">
                                        <Mail
                                            className="mt-0.5 size-3.5 shrink-0 text-amber-700/40"
                                            strokeWidth={2}
                                            aria-hidden
                                        />
                                        <span className="truncate">{voluntario.email}</span>
                                    </p>

                                    {convidados ? (
                                        <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-sm text-amber-900/50">
                                            <span className="inline-flex items-center gap-1.5">
                                                <Calendar
                                                    className="size-3.5 shrink-0 text-amber-700/40"
                                                    strokeWidth={2}
                                                    aria-hidden
                                                />
                                                Enviado em{' '}
                                                {formatarData(
                                                    voluntario.convite_enviado_em ??
                                                        voluntario.created_at,
                                                )}
                                            </span>
                                            <span className="inline-flex items-center gap-1.5">
                                                <Calendar
                                                    className="size-3.5 shrink-0 text-amber-700/40"
                                                    strokeWidth={2}
                                                    aria-hidden
                                                />
                                                Expira em{' '}
                                                {formatarData(
                                                    voluntario.convite_expira_em ??
                                                        undefined,
                                                )}
                                            </span>
                                        </div>
                                    ) : (
                                        <p className="mt-1.5 flex gap-1.5 text-sm leading-relaxed text-amber-900/50">
                                            <MapPin
                                                className="mt-0.5 size-3.5 shrink-0 text-amber-700/40"
                                                strokeWidth={2}
                                                aria-hidden
                                            />
                                            <span>{getCidade(voluntario)}</span>
                                        </p>
                                    )}

                                    <div className="mt-3 flex flex-wrap gap-1.5">
                                        {convidados ? (
                                            <span
                                                className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide ${statusConvite?.className ?? 'border-transparent bg-amber-50 text-amber-700'}`}
                                            >
                                                {statusConvite?.label ?? 'Pendente'}
                                            </span>
                                        ) : (
                                            <>
                                                <span
                                                    className={`inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide ${statusVoluntario.className}`}
                                                >
                                                    {statusVoluntario.label}
                                                </span>
                                                <span className="inline-flex items-center rounded-full border border-amber-200/80 bg-amber-50/80 px-2.5 py-0.5 text-[11px] font-medium uppercase tracking-wide text-amber-900">
                                                    Cadastro {formatarData(voluntario.created_at)}
                                                </span>
                                            </>
                                        )}
                                    </div>
                                </div>

                                {ehAdministrador ? (
                                    <div className="flex shrink-0 items-center justify-between gap-4 border-t border-amber-50 pt-4 sm:w-auto sm:justify-end sm:border-t-0 sm:border-l sm:border-amber-50 sm:pl-6 sm:pt-0">
                                        {convidados &&
                                        podeReenviarConvite(voluntario.statusKey) ? (
                                            <button
                                                type="button"
                                                onClick={() =>
                                                    onReenviarConvite(voluntario)
                                                }
                                                className="inline-flex size-9 items-center justify-center rounded-full text-amber-700 opacity-80 transition hover:bg-amber-50 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                                aria-label={`Reenviar convite para ${voluntario.name}`}
                                                title={`Reenviar convite para ${voluntario.name}`}
                                            >
                                                <MailPlus size={17} strokeWidth={1.75} />
                                            </button>
                                        ) : null}

                                        {!convidados ? (
                                            <Link
                                                href={edit.url(voluntario.id)}
                                                prefetch
                                                className="inline-flex size-9 items-center justify-center rounded-full text-amber-700 opacity-80 transition hover:bg-amber-50 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                                aria-label={`Editar ${voluntario.name}`}
                                            >
                                                <Pencil size={17} strokeWidth={1.75} />
                                            </Link>
                                        ) : null}

                                        {(!convidados ||
                                            podeCancelarConvite(voluntario.statusKey)) && (
                                            <DropdownMenu>
                                                <DropdownMenuTrigger className="inline-flex size-9 items-center justify-center rounded-full text-amber-700 opacity-80 transition hover:bg-amber-50 hover:opacity-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                                    <MoreHorizontal size={17} strokeWidth={1.75} />
                                                    <span className="sr-only">Ações</span>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    {convidados ? (
                                                        <DropdownMenuItem
                                                            onSelect={() =>
                                                                onCancelarConvite(voluntario)
                                                            }
                                                            className="flex cursor-pointer items-center gap-2 text-red-600 focus:text-red-700"
                                                        >
                                                            <Ban className="size-4" />
                                                            Excluir convite
                                                        </DropdownMenuItem>
                                                    ) : (
                                                        <>
                                                            <DropdownMenuItem asChild>
                                                                <Link
                                                                    href={edit.url(voluntario.id)}
                                                                    className="flex cursor-pointer items-center gap-2"
                                                                >
                                                                    <Pencil className="size-4" />
                                                                    Editar
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem
                                                                onSelect={() =>
                                                                    onInativar(voluntario)
                                                                }
                                                                className="flex cursor-pointer items-center gap-2 text-red-600 focus:text-red-700"
                                                            >
                                                                <Ban className="size-4" />
                                                                Inativar
                                                            </DropdownMenuItem>
                                                        </>
                                                    )}
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        )}
                                    </div>
                                ) : null}
                            </div>
                        </article>
                    </li>
                )
            })}
        </ul>
    )
}

export default Cards
