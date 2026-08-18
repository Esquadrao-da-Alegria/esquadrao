// REACT
import { usePage } from '@inertiajs/react';
import { type FC, useMemo, useState } from 'react';

// UI
import {
    painelInputClass,
    painelLabelClass,
} from '@/lib/painelFormFieldClasses';
import {
    labelStatus,
    labelTipo,
    VISITA_STATUS,
    VISITA_TIPOS,
} from '@/lib/visita';

// TIPOS
import type { Cidade, Hospital, SharedData, User } from '@/types';
import type { DadosFormulario, VisitaStatus, VisitaTipo } from '@/types/visita';

const OPCOES_HORARIOS = (() => {
    const lista: string[] = [];
    for (let h = 6; h <= 23; h++) {
        const hh = String(h).padStart(2, '0');
        lista.push(`${hh}:00`, `${hh}:15`, `${hh}:30`, `${hh}:45`);
    }
    return lista;
})();

interface Props {
    data: DadosFormulario;
    errors: Record<string, string | undefined>;
    mode: 'create' | 'edit';
    hospitais: Hospital[];
    cidades?: Cidade[];
    lideres: User[];
    onCampoChange: <K extends keyof DadosFormulario>(
        campo: K,
        valor: DadosFormulario[K],
    ) => void;
}

const Form: FC<Props> = ({
    data,
    errors,
    mode,
    hospitais,
    cidades = [],
    lideres,
    onCampoChange,
}) => {
    const { auth } = usePage<SharedData>().props;
    const userCidadeId = (auth?.user?.cidade_base_id ??
        auth?.user?.voluntario?.cidade_base_id ??
        '') as number | '';

    const hospitalSelecionado = useMemo(() => {
        if (!data.hospital_id) return null;
        return hospitais.find((h) => h.id === Number(data.hospital_id)) ?? null;
    }, [data.hospital_id, hospitais]);

    const [cidadeFiltroId, setCidadeFiltroId] = useState<number | ''>(
        hospitalSelecionado?.cidade_id ?? userCidadeId ?? '',
    );

    const hospitaisFiltrados = useMemo(() => {
        if (!cidadeFiltroId) return hospitais;
        return hospitais.filter(
            (h) => Number(h.cidade_id) === Number(cidadeFiltroId),
        );
    }, [cidadeFiltroId, hospitais]);

    const alasDisponiveis = useMemo(() => {
        return hospitalSelecionado?.alas ?? [];
    }, [hospitalSelecionado]);

    const handleCidadeChange = (valor: number | '') => {
        setCidadeFiltroId(valor);
        onCampoChange('hospital_id', '');
        onCampoChange('ala_unidade_id', null);
    };

    const handleHospitalChange = (valor: number | '') => {
        onCampoChange('hospital_id', valor);
        onCampoChange('ala_unidade_id', null);
    };

    const exigeHospital =
        data.tipo === 'hospital' || data.tipo === 'residencia';

    return (
        <div className="space-y-6">
            <div>
                <label htmlFor="tipo" className={painelLabelClass}>
                    Tipo *
                </label>
                <select
                    id="tipo"
                    required
                    value={data.tipo}
                    onChange={(e) =>
                        onCampoChange('tipo', e.target.value as VisitaTipo)
                    }
                    className={painelInputClass}
                >
                    <option value="">Selecione...</option>
                    {VISITA_TIPOS.map((t) => (
                        <option key={t} value={t}>
                            {labelTipo(t)}
                        </option>
                    ))}
                </select>
                {errors.tipo ? (
                    <p className="mt-1 text-sm text-red-600">{errors.tipo}</p>
                ) : null}
            </div>

            {cidades.length > 0 && (
                <div>
                    <label
                        htmlFor="cidade_filtro_id"
                        className={painelLabelClass}
                    >
                        Filtrar por Cidade
                    </label>
                    <select
                        id="cidade_filtro_id"
                        name="cidade_filtro_id"
                        disabled={mode === 'edit'}
                        value={cidadeFiltroId}
                        onChange={(e) =>
                            handleCidadeChange(
                                e.target.value ? Number(e.target.value) : '',
                            )
                        }
                        className={painelInputClass}
                    >
                        <option value="">Todas as Cidades</option>
                        {cidades.map((c) => (
                            <option key={c.id} value={c.id}>
                                {c.nome}
                            </option>
                        ))}
                    </select>
                </div>
            )}

            <div>
                <label htmlFor="hospital_id" className={painelLabelClass}>
                    {exigeHospital ? 'Hospital *' : 'Hospital (Opcional)'}
                </label>
                <select
                    id="hospital_id"
                    name="hospital_id"
                    required={exigeHospital}
                    disabled={mode === 'edit' && exigeHospital}
                    value={data.hospital_id}
                    onChange={(e) =>
                        handleHospitalChange(
                            e.target.value ? Number(e.target.value) : '',
                        )
                    }
                    className={painelInputClass}
                >
                    <option value="">
                        {!exigeHospital
                            ? 'Nenhum hospital (Opcional)'
                            : cidadeFiltroId
                              ? 'Selecione um hospital da cidade...'
                              : 'Selecione...'}
                    </option>
                    {hospitaisFiltrados.map((h) => (
                        <option key={h.id} value={h.id}>
                            {h.nome}
                        </option>
                    ))}
                </select>
                {errors.hospital_id ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.hospital_id}
                    </p>
                ) : null}
            </div>

            <div>
                <label htmlFor="ala_unidade_id" className={painelLabelClass}>
                    Ala / Unidade
                </label>
                <select
                    id="ala_unidade_id"
                    name="ala_unidade_id"
                    disabled={mode === 'edit' || !data.hospital_id}
                    value={data.ala_unidade_id ?? ''}
                    onChange={(e) =>
                        onCampoChange(
                            'ala_unidade_id',
                            e.target.value ? Number(e.target.value) : null,
                        )
                    }
                    className={painelInputClass}
                >
                    <option value="">Nenhuma</option>
                    {alasDisponiveis.map((a) => (
                        <option key={a.id} value={a.id}>
                            {a.nome}
                        </option>
                    ))}
                </select>
                {errors.ala_unidade_id ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.ala_unidade_id}
                    </p>
                ) : null}
            </div>

            <div className="grid gap-6 sm:grid-cols-3">
                <div>
                    <label htmlFor="data" className={painelLabelClass}>
                        Data *
                    </label>
                    <input
                        type="date"
                        id="data"
                        required
                        value={data.data}
                        onChange={(e) => onCampoChange('data', e.target.value)}
                        className={painelInputClass}
                    />
                    {errors.data ? (
                        <p className="mt-1 text-sm text-red-600">
                            {errors.data}
                        </p>
                    ) : null}
                </div>
                <div>
                    <label htmlFor="hora_inicio" className={painelLabelClass}>
                        Início *
                    </label>
                    <select
                        id="hora_inicio"
                        name="hora_inicio"
                        required
                        value={data.hora_inicio}
                        onChange={(e) =>
                            onCampoChange('hora_inicio', e.target.value)
                        }
                        className={painelInputClass}
                    >
                        <option value="">Selecione...</option>
                        {data.hora_inicio &&
                            !OPCOES_HORARIOS.includes(data.hora_inicio) && (
                                <option value={data.hora_inicio}>
                                    {data.hora_inicio}
                                </option>
                            )}
                        {OPCOES_HORARIOS.map((h) => (
                            <option key={h} value={h}>
                                {h}
                            </option>
                        ))}
                    </select>
                    {errors.hora_inicio ? (
                        <p className="mt-1 text-sm text-red-600">
                            {errors.hora_inicio}
                        </p>
                    ) : null}
                </div>
                <div>
                    <label htmlFor="hora_fim" className={painelLabelClass}>
                        Fim *
                    </label>
                    <select
                        id="hora_fim"
                        name="hora_fim"
                        required
                        value={data.hora_fim}
                        onChange={(e) =>
                            onCampoChange('hora_fim', e.target.value)
                        }
                        className={painelInputClass}
                    >
                        <option value="">Selecione...</option>
                        {data.hora_fim &&
                            !OPCOES_HORARIOS.includes(data.hora_fim) && (
                                <option value={data.hora_fim}>
                                    {data.hora_fim}
                                </option>
                            )}
                        {OPCOES_HORARIOS.map((h) => (
                            <option key={h} value={h}>
                                {h}
                            </option>
                        ))}
                    </select>
                    {errors.hora_fim ? (
                        <p className="mt-1 text-sm text-red-600">
                            {errors.hora_fim}
                        </p>
                    ) : null}
                </div>
            </div>

            <div>
                <label
                    htmlFor="limite_participantes"
                    className={painelLabelClass}
                >
                    Limite de Participantes
                </label>
                <input
                    type="number"
                    id="limite_participantes"
                    name="limite_participantes"
                    min={1}
                    placeholder="Deixe em branco para ilimitado (padrão: 5)"
                    value={data.limite_participantes ?? ''}
                    onChange={(e) =>
                        onCampoChange(
                            'limite_participantes',
                            e.target.value !== '' ? Number(e.target.value) : '',
                        )
                    }
                    className={painelInputClass}
                />
                <p className="mt-1 text-xs text-gray-500">
                    Deixe em branco para ilimitado ou informe um número de
                    vagas.
                </p>
                {errors.limite_participantes ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.limite_participantes}
                    </p>
                ) : null}
            </div>

            <div>
                <label htmlFor="lider_id" className={painelLabelClass}>
                    Líder *
                </label>
                <select
                    id="lider_id"
                    required
                    value={data.lider_id}
                    onChange={(e) =>
                        onCampoChange(
                            'lider_id',
                            e.target.value ? Number(e.target.value) : '',
                        )
                    }
                    className={painelInputClass}
                >
                    <option value="">Selecione...</option>
                    {lideres.map((l) => (
                        <option key={l.id} value={l.id}>
                            {l.name}
                        </option>
                    ))}
                </select>
                {errors.lider_id ? (
                    <p className="mt-1 text-sm text-red-600">
                        {errors.lider_id}
                    </p>
                ) : null}
            </div>

            {mode === 'edit' && data.status ? (
                <div>
                    <label htmlFor="status" className={painelLabelClass}>
                        Status *
                    </label>
                    <select
                        id="status"
                        required
                        value={data.status}
                        onChange={(e) =>
                            onCampoChange(
                                'status',
                                e.target.value as VisitaStatus,
                            )
                        }
                        className={painelInputClass}
                    >
                        {VISITA_STATUS.map((s) => (
                            <option key={s} value={s}>
                                {labelStatus(s)}
                            </option>
                        ))}
                    </select>
                    {errors.status ? (
                        <p className="mt-1 text-sm text-red-600">
                            {errors.status}
                        </p>
                    ) : null}
                </div>
            ) : null}

            <div>
                <label htmlFor="observacoes" className={painelLabelClass}>
                    Observações
                </label>
                <textarea
                    id="observacoes"
                    rows={4}
                    value={data.observacoes}
                    onChange={(e) =>
                        onCampoChange('observacoes', e.target.value)
                    }
                    className={`${painelInputClass} resize-none`}
                />
            </div>
        </div>
    );
};

export default Form;
