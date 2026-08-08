import {
    meu,
    visaoGeral,
    visitasPorHospital,
    visitasPorParticipante,
} from '@/routes/dashboards';
import { ChartNoAxesCombined, Hospital, UserRound } from 'lucide-react';

import type { PermissaoDashboard } from '@/types';

export const itensDashboards = [
    {
        titulo: 'Meu dashboard',
        href: meu(),
        caminho: '/dashboards/meu',
        icone: UserRound,
        permissao: 'dashboard.meu',
    },
    {
        titulo: 'Visão geral',
        href: visaoGeral(),
        caminho: '/dashboards/visao-geral',
        icone: ChartNoAxesCombined,
        permissao: 'dashboard.visao_geral',
    },
    {
        titulo: 'Visitas por hospital',
        href: visitasPorHospital(),
        caminho: '/dashboards/visitas-por-hospital',
        icone: Hospital,
        permissao: 'dashboard.visitas_por_hospital',
    },
    {
        titulo: 'Visitas por participante',
        href: visitasPorParticipante(),
        caminho: '/dashboards/visitas-por-participante',
        icone: ChartNoAxesCombined,
        permissao: 'dashboard.visitas_por_participante',
    },
] satisfies Array<{
    titulo: string;
    href: ReturnType<typeof meu>;
    caminho: string;
    icone: typeof UserRound;
    permissao: PermissaoDashboard;
}>;
