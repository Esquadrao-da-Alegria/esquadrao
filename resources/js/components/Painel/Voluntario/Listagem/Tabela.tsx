import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { edit } from '@/routes/voluntarios';
import { Link } from '@inertiajs/react';
import { Ban, MailPlus, MoreHorizontal, Pencil } from 'lucide-react';
import {
    formatarData,
    getCidade,
    getIniciais,
    getStatusVoluntario,
    podeCancelarConvite,
    podeReenviarConvite,
    statusMap,
} from './status';
import { AbaKey, VoluntarioListagem } from './types';

interface Props {
    aba: AbaKey;
    voluntarios: VoluntarioListagem[];
    ehAdministrador: boolean;
    onInativar: (voluntario: VoluntarioListagem) => void;
    onReenviarConvite: (voluntario: VoluntarioListagem) => void;
    onCancelarConvite: (voluntario: VoluntarioListagem) => void;
}

const Tabela: React.FC<Props> = ({
    aba,
    voluntarios,
    ehAdministrador,
    onInativar,
    onReenviarConvite,
    onCancelarConvite,
}) => {
    const convidados = aba === 'convidados';

    return (
        <section className="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50/80">
                        <tr>
                            <th className="px-5 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                Voluntário
                            </th>
                            <th className="px-5 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                {convidados ? 'Status do convite' : 'Cidade'}
                            </th>
                            <th className="px-5 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                {convidados ? 'Data do envio' : 'Status'}
                            </th>
                            <th className="px-5 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                {convidados ? 'Expiração' : 'Data de cadastro'}
                            </th>
                            {ehAdministrador ? (
                                <th className="px-5 py-4 text-left text-xs font-semibold tracking-wide text-gray-500 uppercase">
                                    Ações
                                </th>
                            ) : null}
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100 bg-white">
                        {voluntarios.length === 0 ? (
                            <tr>
                                <td
                                    colSpan={ehAdministrador ? 5 : 4}
                                    className="px-5 py-12 text-center text-sm text-gray-500"
                                >
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        ) : (
                            voluntarios.map((voluntario) => {
                                const statusConvite = statusMap.get(
                                    voluntario.statusKey,
                                );
                                const statusVoluntario =
                                    getStatusVoluntario(voluntario);

                                return (
                                    <tr
                                        key={voluntario.id}
                                        className="transition hover:bg-amber-50/30"
                                    >
                                        <td className="px-5 py-4">
                                            <div className="flex items-center gap-3">
                                                <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-rose-50 text-xs font-semibold text-gray-700 ring-1 ring-rose-100">
                                                    {getIniciais(
                                                        voluntario.name,
                                                    )}
                                                </div>
                                                <div className="min-w-0">
                                                    <p className="truncate text-sm font-semibold text-gray-950">
                                                        {voluntario.name}
                                                    </p>
                                                    <p className="truncate text-sm text-gray-500">
                                                        {voluntario.email}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-5 py-4">
                                            {convidados ? (
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${statusConvite?.className ?? 'bg-gray-100 text-gray-700'}`}
                                                >
                                                    {statusConvite?.label ??
                                                        'Pendente'}
                                                </span>
                                            ) : (
                                                <span className="text-sm text-gray-500">
                                                    {getCidade(voluntario)}
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-5 py-4 text-sm text-gray-500">
                                            {convidados ? (
                                                formatarData(
                                                    voluntario.convite_enviado_em ??
                                                        voluntario.created_at,
                                                )
                                            ) : (
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-medium ${statusVoluntario.className}`}
                                                >
                                                    {statusVoluntario.label}
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-5 py-4 text-sm text-gray-500">
                                            {convidados
                                                ? formatarData(
                                                      voluntario.convite_expira_em ??
                                                          undefined,
                                                  )
                                                : formatarData(
                                                      voluntario.created_at,
                                                  )}
                                        </td>
                                        {ehAdministrador ? (
                                            <td className="px-5 py-4">
                                                <div className="flex items-center gap-1.5 text-gray-500">
                                                    {convidados &&
                                                    podeReenviarConvite(
                                                        voluntario.statusKey,
                                                    ) ? (
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                onReenviarConvite(
                                                                    voluntario,
                                                                )
                                                            }
                                                            className="inline-flex size-8 items-center justify-center rounded-full transition hover:bg-amber-50 hover:text-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500"
                                                            aria-label={`Reenviar convite para ${voluntario.name}`}
                                                            title={`Reenviar convite para ${voluntario.name}`}
                                                        >
                                                            <MailPlus className="size-4" />
                                                        </button>
                                                    ) : null}
                                                    {(!convidados ||
                                                        podeCancelarConvite(
                                                            voluntario.statusKey,
                                                        )) && (
                                                        <DropdownMenu>
                                                            <DropdownMenuTrigger className="inline-flex size-8 items-center justify-center rounded-full transition hover:bg-amber-50 hover:text-amber-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">
                                                                <MoreHorizontal className="size-4" />
                                                                <span className="sr-only">
                                                                    Ações
                                                                </span>
                                                            </DropdownMenuTrigger>
                                                            <DropdownMenuContent align="end">
                                                                {convidados ? (
                                                                    <DropdownMenuItem
                                                                        onSelect={() =>
                                                                            onCancelarConvite(
                                                                                voluntario,
                                                                            )
                                                                        }
                                                                        className="flex cursor-pointer items-center gap-2 text-red-600 focus:text-red-700"
                                                                    >
                                                                        <Ban className="size-4" />
                                                                        Excluir
                                                                        convite
                                                                    </DropdownMenuItem>
                                                                ) : (
                                                                    <>
                                                                        <DropdownMenuItem
                                                                            asChild
                                                                        >
                                                                            <Link
                                                                                href={edit.url(
                                                                                    voluntario.id,
                                                                                )}
                                                                                className="flex cursor-pointer items-center gap-2"
                                                                            >
                                                                                <Pencil className="size-4" />
                                                                                Editar
                                                                            </Link>
                                                                        </DropdownMenuItem>
                                                                        <DropdownMenuSeparator />
                                                                        <DropdownMenuItem
                                                                            onSelect={() =>
                                                                                onInativar(
                                                                                    voluntario,
                                                                                )
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
                                            </td>
                                        ) : null}
                                    </tr>
                                );
                            })
                        )}
                    </tbody>
                </table>
            </div>
        </section>
    );
};

export default Tabela;
