import { temCargo } from '@/lib/utils/user';
import type { User } from '@/types';
import type {
    TipoRelatorio,
    Visita,
    VisitaParticipante,
    VisitaRelatorio,
    VisitaStatus,
    VisitaTipo,
} from '@/types/visita';

export const LIMITE_PARTICIPANTES = 5;

const STATUS_ATIVOS = ['confirmado', 'pendente'];

export function listarParticipantesAtivos(
    visita: Visita,
): VisitaParticipante[] {
    return (
        visita.participantes?.filter(
            (p) =>
                p.papel_na_visita === 'participante' &&
                STATUS_ATIVOS.includes(p.status_participacao),
        ) ?? []
    );
}

export function contarParticipantesAtivos(visita: Visita): number {
    return listarParticipantesAtivos(visita).length;
}

export function visitaAtingiuLimite(visita: Visita): boolean {
    if (
        visita.limite_participantes === null ||
        visita.limite_participantes === undefined
    ) {
        return false;
    }
    return contarParticipantesAtivos(visita) >= visita.limite_participantes;
}

export function usuarioJaInscrito(visita: Visita, usuarioId: number): boolean {
    return listarParticipantesAtivos(visita).some(
        (p) => p.voluntario_id === usuarioId,
    );
}

export function participacaoAtivaDoUsuario(
    visita: Visita,
    usuarioId: number,
): VisitaParticipante | null {
    return (
        listarParticipantesAtivos(visita).find(
            (p) => p.voluntario_id === usuarioId,
        ) ?? null
    );
}

export function usuarioEhLiderDaVisita(
    visita: Visita,
    usuarioId: number,
): boolean {
    return (
        visita.lider_id !== null &&
        visita.lider_id !== undefined &&
        visita.lider_id === usuarioId
    );
}

export function contarParticipantes(visita: Visita) {
    const lista = listarParticipantesAtivos(visita);

    return {
        palhaco: lista.filter((p) => p.tipo_participacao === 'palhaco').length,
        paisana: lista.filter((p) => p.tipo_participacao === 'paisana').length,
    };
}

export function tituloVisita(visita: Visita): string {
    if (visita.tipo === 'acao_especial') {
        return visita.hospital?.nome
            ? `Ação Especial - ${visita.hospital.nome}`
            : 'Ação Especial';
    }
    return visita.hospital?.nome ?? 'Visita';
}

export function classeCardPorOcupacao(visita: Visita): string {
    const participantes = contarParticipantesAtivos(visita);
    const limite = visita.limite_participantes;

    if (visita.status === 'cancelada') {
        return 'border-gray-300 bg-gray-100 text-gray-600';
    }

    if (visita.tipo === 'acao_especial') {
        return visita.status === 'agendada'
            ? 'border-purple-700 bg-purple-600 text-white'
            : 'border-purple-200 bg-purple-100 text-purple-800';
    }

    if (visita.status === 'realizada' || visita.status === 'contabilizada') {
        return 'border-green-200 bg-green-100 text-green-800';
    }

    if (
        visita.status === 'pendente_relatorio' ||
        visita.status === 'nao_contabilizada'
    ) {
        return 'border-orange-200 bg-orange-100 text-orange-800';
    }
    if (limite === null || limite === undefined) {
        return 'border-sky-500 bg-sky-500 text-white';
    }

    if (participantes >= limite) {
        return 'border-red-600 bg-red-600 text-white';
    }

    if (participantes === 1) {
        return 'border-sky-500 bg-sky-500 text-white';
    }

    if (participantes >= 2) {
        return 'border-orange-500 bg-orange-500 text-white';
    }

    return 'border-sky-500 bg-sky-500 text-white';
}

export function classeIndicadorPorOcupacao(visita: Visita): string {
    const classe = classeCardPorOcupacao(visita);

    if (classe.includes('sky')) return 'bg-sky-500';
    if (classe.includes('green')) return 'bg-green-400';
    if (classe.includes('red')) return 'bg-red-600';
    if (classe.includes('orange-100')) return 'bg-orange-300';
    if (classe.includes('orange')) return 'bg-orange-500';
    if (classe.includes('purple-100')) return 'bg-purple-300';
    if (classe.includes('purple')) return 'bg-purple-600';

    return 'bg-gray-400';
}

export function labelStatus(status: VisitaStatus): string {
    const labels: Record<VisitaStatus, string> = {
        agendada: 'Agendada',
        realizada: 'Realizada',
        cancelada: 'Cancelada',
        pendente_relatorio: 'Pend. relatório',
        contabilizada: 'Contabilizada',
        nao_contabilizada: 'Não contabilizada',
    };
    return labels[status] ?? status;
}

export function podeEditarVisita(user: User, visita: Visita): boolean {
    if (
        visita.lider_id !== null &&
        visita.lider_id !== undefined &&
        user.id === visita.lider_id
    ) {
        return true;
    }

    if (
        temCargo(user, 'administrador') ||
        temCargo(user, 'diretor') ||
        temCargo(user, 'coordenador_geral')
    ) {
        return true;
    }

    if (temCargo(user, 'coordenador_local')) {
        const cidadeUsuario = user.voluntario?.cidade_base_id;
        const cidadeHospital = visita.hospital?.cidade_id;

        return (
            cidadeUsuario != null &&
            cidadeHospital != null &&
            cidadeUsuario === cidadeHospital
        );
    }

    return false;
}

export function labelTipo(tipo: VisitaTipo): string {
    const labels: Record<VisitaTipo, string> = {
        hospital: 'Hospital',
        residencia: 'Residência',
        acao_especial: 'Ação especial',
        outro: 'Outro',
    };
    return labels[tipo] ?? tipo;
}

export function extrairData(iso: string): string {
    return iso.slice(0, 10);
}

export function mesDaData(data: string): string {
    return data.slice(0, 7)
}

export function extrairDia(data: string): number {
    return Number(data.slice(8, 10))
}

export function diasDoMes(anoMes: string): number[] {
    const [ano, mes] = anoMes.split('-').map(Number)
    const totalDias = new Date(ano, mes, 0).getDate()

    return Array.from({ length: totalDias }, (_, indice) => indice + 1)
}

export function montarData(anoMes: string, dia: number): string {
    return `${anoMes}-${String(dia).padStart(2, '0')}`
}

export function labelMes(anoMes: string): string {
    const [ano, mes] = anoMes.split('-').map(Number)
    const nome = new Date(ano, mes - 1, 1).toLocaleDateString('pt-BR', { month: 'long' })

    return nome.charAt(0).toUpperCase() + nome.slice(1)
}

export function labelAnoMes(anoMes: string): string {
    const [ano, mes] = anoMes.split('-').map(Number)

    return new Date(ano, mes - 1, 1).toLocaleDateString('pt-BR', {
        month: 'long',
        year: 'numeric',
    })
}

export function mesesLiberadosParaSelecao(
    mesesLiberados: string[],
    dataAtual?: string,
): string[] {
    const meses = [...mesesLiberados]

    if (dataAtual) {
        const mesAtual = mesDaData(dataAtual)

        if (mesAtual && !meses.includes(mesAtual)) {
            meses.push(mesAtual)
        }
    }

    return meses.sort()
}

export function dataPermitidaVisitaHospital(data: string, mesesLiberados: string[]): boolean {
    if (!data || mesesLiberados.length === 0) {
        return false
    }

    return mesesLiberados.includes(mesDaData(data))
}

export function ultimoDiaDoMes(anoMes: string): string {
    const [ano, mes] = anoMes.split('-').map(Number)
    const ultimoDia = new Date(ano, mes, 0).getDate()

    return `${anoMes}-${String(ultimoDia).padStart(2, '0')}`
}

export function intervaloDatasLiberadas(mesesLiberados: string[]): { min: string; max: string } | null {
    if (mesesLiberados.length === 0) {
        return null
    }

    const ordenados = [...mesesLiberados].sort()
    const primeiro = ordenados[0]
    const ultimo = ordenados[ordenados.length - 1]

    return {
        min: `${primeiro}-01`,
        max: ultimoDiaDoMes(ultimo),
    }
}

export function hojeLocal(): string {
    const d = new Date();
    const mes = String(d.getMonth() + 1).padStart(2, '0');
    const dia = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${mes}-${dia}`;
}

export function extrairHora(iso: string): string {
    return iso.slice(11, 16);
}

export const VISITA_TIPOS: VisitaTipo[] = [
    'hospital',
    'residencia',
    'acao_especial',
    'outro',
];

export const VISITA_STATUS: VisitaStatus[] = [
    'agendada',
    'realizada',
    'cancelada',
    'pendente_relatorio',
    'contabilizada',
    'nao_contabilizada',
];

export const TIPOS_RELATORIO: TipoRelatorio[] = ['palhaco', 'paisana', 'geral'];

export function labelTipoRelatorio(tipo: TipoRelatorio): string {
    const labels: Record<TipoRelatorio, string> = {
        palhaco: 'Palhaço',
        paisana: 'Paisana',
        geral: 'Geral',
    };
    return labels[tipo] ?? tipo;
}

export function podeEditarRelatorio(
    user: User,
    visita: Visita,
    relatorio: VisitaRelatorio,
): boolean {
    if (visita.status === 'cancelada') {
        return false;
    }

    if (relatorio.autor_id === user.id) {
        return true;
    }

    return podeEditarVisita(user, visita);
}

export function usuarioParticipouDaVisita(
    visita: Visita,
    usuarioId: number,
): boolean {
    if (usuarioEhLiderDaVisita(visita, usuarioId)) {
        return true;
    }
    return usuarioJaInscrito(visita, usuarioId);
}

export function podeCriarRelatorio(user: User, visita: Visita): boolean {
    if (visita.status === 'cancelada') {
        return false;
    }
    if (podeEditarVisita(user, visita)) {
        return true;
    }
    return usuarioParticipouDaVisita(visita, user.id);
}

export function formatarDataHora(iso: string): string {
    return new Date(iso).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}
