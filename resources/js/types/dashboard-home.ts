export interface AtividadeVisaoGeral {
    id: number;
    categoria: 'visita' | 'oficina' | 'reuniao';
    titulo: string;
    inicio_em: string;
    fim_em: string;
    local: string;
    cidade: string | null;
    situacao: string;
    detalhes_url: string;
}

export interface PendenciaVisaoGeral {
    id: string;
    tipo: 'relatorio';
    titulo: string;
    descricao: string;
    prazo_em: string;
    estado_prazo: 'em_prazo' | 'prazo_proximo' | 'atrasado';
    acao: { titulo: string; url: string };
}

export interface AvisoVisaoGeral {
    id: string;
    titulo: string;
    mensagem: string;
    tipo: string;
    link?: string | null;
}
