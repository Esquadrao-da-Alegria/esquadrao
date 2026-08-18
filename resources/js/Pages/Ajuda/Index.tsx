import PainelLayout from '@/layouts/PainelLayout';
import { Head, Link } from '@inertiajs/react';
import {
    BookOpen,
    ChevronDown,
    ChevronRight,
    CircleHelp,
    HeartHandshake,
    Info,
    Mail,
    MapPin,
    Search,
    Shield,
    Sparkles,
    Users,
} from 'lucide-react';
import { useState } from 'react';

interface ManualItem {
    id: string;
    titulo: string;
    subtitulo: string;
    categoria: 'voluntario' | 'coordenador' | 'implantacao';
    tags: string[];
    passos: {
        passo: number;
        titulo: string;
        descricao: string;
        dica?: string;
    }[];
    linkAcao?: {
        label: string;
        href: string;
    };
}

const tutoriais: ManualItem[] = [
    // --- MANUAL DO VOLUNTÁRIO ---
    {
        id: 'vol-convite',
        titulo: 'Como aceitar o convite e concluir seu cadastro',
        subtitulo: 'Passo a passo para novos voluntários do Esquadrão',
        categoria: 'voluntario',
        tags: ['convite', 'cadastro', 'primeiro acesso', 'senha', 'e-mail'],
        passos: [
            {
                passo: 1,
                titulo: 'Recebimento do Convite por E-mail',
                descricao:
                    'Você receberá um e-mail com um link de convite exclusivo gerado pela coordenação do seu grupo.',
                dica: 'Caso não encontre na caixa de entrada, verifique sua pasta de Spam ou Lixo Eletrônico.',
            },
            {
                passo: 2,
                titulo: 'Preenchimento dos Dados Pessoais',
                descricao:
                    'Clique no link do e-mail. Você será direcionado para um formulário onde informará seus dados (nome completo, CPF, telefone, data de nascimento e cidade).',
            },
            {
                passo: 3,
                titulo: 'Criação de Senha e Confirmação',
                descricao:
                    'Defina uma senha segura para acessar o painel do Esquadrão e clique em "Concluir Cadastro".',
                dica: 'Guarde sua senha em local seguro. Ela será usada em todos os seus acessos.',
            },
        ],
    },
    {
        id: 'vol-eventos',
        titulo: 'Como se inscrever em Reuniões, Oficinas e Treinamentos',
        subtitulo: 'Garantindo sua participação nas atividades do seu grupo',
        categoria: 'voluntario',
        tags: ['eventos', 'oficinas', 'reuniões', 'inscrição', 'vagas'],
        linkAcao: { label: 'Ver Eventos', href: '/eventos' },
        passos: [
            {
                passo: 1,
                titulo: 'Acessar o menu Eventos',
                descricao:
                    'No menu principal do painel, clique em "Eventos" para ver todas as atividades agendadas para sua cidade.',
            },
            {
                passo: 2,
                titulo: 'Escolher a atividade desejada',
                descricao:
                    'Confira a data, horário, local, limite de vagas e data limite para inscrição.',
            },
            {
                passo: 3,
                titulo: 'Confirmar Inscrição',
                descricao:
                    'Clique no botão "Quero me inscrever". Sua presença ficará registrada na lista de inscritos.',
                dica: 'Lembre-se: para permanecer ativo no grupo, é necessário ter no mínimo 50% de presença nas oficinas a cada semestre.',
            },
            {
                passo: 4,
                titulo: 'Cancelamento de Inscrição (se necessário)',
                descricao:
                    'Caso tenha um imprevisto e não possa comparecer, cancele sua inscrição antes da data limite para liberar a vaga a outro voluntário.',
            },
        ],
    },
    {
        id: 'vol-visitas',
        titulo: 'Como visualizar suas Visitas Hospitalares agendadas',
        subtitulo: 'Consultando o calendário e os hospitais escalados',
        categoria: 'voluntario',
        tags: ['visitas', 'hospital', 'escala', 'agenda', 'participante'],
        linkAcao: { label: 'Minhas Visitas', href: '/visitas' },
        passos: [
            {
                passo: 1,
                titulo: 'Acessar a aba Visitas',
                descricao: 'Clique no menu "Visitas" no painel interno.',
            },
            {
                passo: 2,
                titulo: 'Consultar o Calendário ou Lista',
                descricao:
                    'Você verá o calendário de visitas com hospital, ala/unidade e os voluntários escalados para cada data.',
            },
            {
                passo: 3,
                titulo: 'Identificar o Líder da Visita',
                descricao:
                    'Toda visita tem um Líder responsável nomeado. Verifique quem é o líder da sua visita para alinhamento antes do horário.',
            },
        ],
    },

    {
        id: 'vol-metas-regras',
        titulo: 'Como funciona o cálculo de Metas, Saldos e Compensações',
        subtitulo:
            'Regras de 2 visitas/mês, compensação dentro do semestre e saldo de créditos',
        categoria: 'voluntario',
        tags: ['meta', 'compensacao', 'semestre', 'saldo', 'credito', 'regra'],
        passos: [
            {
                passo: 1,
                titulo: 'Meta Sugerida de 2 Visitas por Mês',
                descricao:
                    'Cada voluntário ativo tem como meta sugerida realizar 2 visitas hospitalares válidas a cada mês.',
            },
            {
                passo: 2,
                titulo: 'Compensação Flexível dentro do Semestre',
                descricao:
                    'As visitas podem ser compensadas dentro do mesmo semestre (1º Semestre: Jan–Jun | 2º Semestre: Jul–Dez). Por exemplo: realizar 4 visitas em Julho cumpre a meta de Julho e transfere 2 créditos para Agosto, cobrindo a meta de Agosto!',
                dica: 'Meses com mais visitas compensam meses sem visitas, garantindo flexibilidade na rotina.',
            },
            {
                passo: 3,
                titulo: 'Cálculo a partir da Data de Ingresso',
                descricao:
                    'Para novos voluntários ou ao ativar novos grupos (ex: Porto Alegre, Pelotas), o cálculo de metas e compensações inicia exatamente no mês em que o voluntário entrou na ONG (data_entrada_ong), sem cobranças retroativas de meses anteriores.',
            },
        ],
    },

    // --- MANUAL DO COORDENADOR / ADMINISTRADOR ---
    {
        id: 'adm-convites',
        titulo: 'Como enviar convites para novos voluntários',
        subtitulo: 'Cadastrando novos integrantes do grupo',
        categoria: 'coordenador',
        tags: ['convite', 'novo voluntario', 'e-mail', 'token', 'coordenador'],
        linkAcao: { label: 'Gerenciar Voluntários', href: '/voluntarios' },
        passos: [
            {
                passo: 1,
                titulo: 'Acessar a Gestão de Voluntários',
                descricao:
                    'No menu lateral, acesse "Voluntários" e clique no botão "Enviar Convite".',
            },
            {
                passo: 2,
                titulo: 'Preencher Nome, E-mail e Cidade',
                descricao:
                    'Digite o e-mail e o nome do novo voluntário e selecione a cidade/grupo correspondente (ex: Santa Maria).',
            },
            {
                passo: 3,
                titulo: 'Enviar ou Copiar Link de Convite',
                descricao:
                    'O sistema enviará o e-mail automaticamente. Você também pode copiar o link do convite gerado e enviar por WhatsApp se desejar.',
                dica: 'Você pode reenviar ou cancelar convites pendentes a qualquer momento na aba de Convites.',
            },
        ],
    },
    {
        id: 'adm-afastamentos',
        titulo: 'Como gerenciar Afastamentos e Atestados Médicos de Voluntários',
        subtitulo:
            'Cadastrando licenças temporárias, prorrogações e cancelamento automático de visitas',
        categoria: 'coordenador',
        tags: [
            'afastamento',
            'atestado',
            'licença',
            'médico',
            'prorrogação',
            'bloqueio',
            'visita',
            'cancelamento',
        ],
        linkAcao: { label: 'Gerenciar Voluntários', href: '/voluntarios' },
        passos: [
            {
                passo: 1,
                titulo: 'Acessar o menu de Ações do Voluntário',
                descricao:
                    'No menu "Voluntários", localize o integrante desejado, clique no menu de ações (três pontinhos) no card do voluntário e selecione "Gerenciar afastamento".',
            },
            {
                passo: 2,
                titulo: 'Cadastrar Novo Afastamento ou Atestado',
                descricao:
                    'Informe a Data de Início, a Data de Fim (ou use os atalhos rápidos de +15, +30 ou +60 dias), o Motivo (Atestado Médico, Licença Pessoal, Estudos ou Outro) e observações relevantes.',
                dica: 'Aviso importante: ao salvar o afastamento, todas as inscrições agendadas do voluntário em visitas hospitalares dentro do período são canceladas automaticamente para liberar vagas.',
            },
            {
                passo: 3,
                titulo: 'Visualização e Filtros de Afastamento',
                descricao:
                    'Voluntários com licença ativa exibem um badge visual destacado ("Afastado - Atestado até DD/MM/AAAA"). Você pode filtrar a listagem selecionando "Apenas Afastados" no filtro de status.',
            },
            {
                passo: 4,
                titulo: 'Prorrogar Prazo de Afastamento',
                descricao:
                    'Caso o voluntário apresente um novo atestado ou necessite de mais tempo, abra o modal de afastamento, acerte a "Nova Data Final" na aba Prorrogar e confirme. O histórico com as datas anteriores é preservado.',
            },
            {
                passo: 5,
                titulo: 'Encerramento Antecipado',
                descricao:
                    'Se o voluntário receber alta médica antes do prazo previsto, selecione a aba "Encerrar Antecipadamente" e confirme. A data de término será ajustada para a data atual e o voluntário poderá voltar a se inscrever em visitas normalmente.',
            },
        ],
    },
    {
        id: 'adm-eventos',
        titulo: 'Como criar Oficinas e Reuniões e registrar presenças',
        subtitulo: 'Gerenciando eventos, lista de chamada e finalização',
        categoria: 'coordenador',
        tags: [
            'criar evento',
            'oficina',
            'reunião',
            'chamada',
            'presença',
            'finalizar',
        ],
        linkAcao: { label: 'Criar Evento', href: '/eventos/create' },
        passos: [
            {
                passo: 1,
                titulo: 'Criar Novo Evento',
                descricao:
                    'Acesse "Eventos" > "Criar Evento". Preencha o título (ex: "Oficina de Palhaçaria de Julho"), tipo, data/hora inicio e fim, local, cidade e limite de participantes.',
            },
            {
                passo: 2,
                titulo: 'Acompanhar Inscrições',
                descricao:
                    'Na página de detalhes do evento, veja em tempo real quais voluntários se inscreveram.',
            },
            {
                passo: 3,
                titulo: 'Registrar Presenças (Chamada)',
                descricao:
                    'No dia ou após o evento, acesse o evento e marque quem esteve presente e quem faltou.',
            },
            {
                passo: 4,
                titulo: 'Finalizar Evento',
                descricao:
                    'Após registrar as presenças, clique em "Finalizar Evento". O status passará para finalizado e as presenças serão contabilizadas no histórico dos voluntários.',
            },
        ],
    },
    {
        id: 'adm-visitas',
        titulo: 'Como agendar Visitas Hospitalares e escalar voluntários',
        subtitulo: 'Organizando a escala hospitalar e líderes',
        categoria: 'coordenador',
        tags: ['agendar visita', 'escala', 'hospital', 'ala', 'líder'],
        linkAcao: { label: 'Agendar Visita', href: '/visitas/create' },
        passos: [
            {
                passo: 1,
                titulo: 'Cadastrar Hospitais e Alas (se necessário)',
                descricao:
                    'Antes de agendar visitas, certifique-se de que os Hospitais e suas respectivas Alas/Unidades estão cadastrados na aba "Hospitais".',
            },
            {
                passo: 2,
                titulo: 'Agendar Visita',
                descricao:
                    'Acesse "Visitas" > "Nova Visita". Escolha o hospital, a ala/unidade, a data/hora e atribua o Líder da visita.',
            },
            {
                passo: 3,
                titulo: 'Adicionar Participantes na Escala',
                descricao:
                    'Insira os voluntários escalados para aquela visita na aba de participantes.',
            },
        ],
    },
    {
        id: 'adm-relatorios',
        titulo: 'Como preencher e exportar Relatórios de Visita em PDF',
        subtitulo:
            'Registrando ocorrências, interações e gerando o documento oficial',
        categoria: 'coordenador',
        tags: ['relatório', 'pdf', 'visita', 'atendimentos', 'ocorrências'],
        linkAcao: { label: 'Ir para Visitas', href: '/visitas' },
        passos: [
            {
                passo: 1,
                titulo: 'Acessar a Visita Concluída',
                descricao:
                    'Após o término da visita no hospital, o líder ou coordenador entra nos detalhes da visita no painel.',
            },
            {
                passo: 2,
                titulo: 'Criar Relatório de Visita',
                descricao:
                    'Clique em "Criar Relatório". Informe a quantidade de atendimentos/interações realizadas, a dinâmica da visita e observações relevantes.',
            },
            {
                passo: 3,
                titulo: 'Gerar e Baixar PDF',
                descricao:
                    'Após salvar o relatório, clique em "Baixar PDF" para gerar o documento formatado oficial da ONG.',
                dica: 'Os relatórios em PDF podem ser arquivados e enviados para a coordenação geral ou hospitais parceiros.',
            },
        ],
    },
    {
        id: 'adm-retroativos-eventos',
        titulo: 'Como cadastrar Visitas, Reuniões e Oficinas retroativas e lançar presenças',
        subtitulo:
            'Regularizando atividades realizadas em meses passados para fechar o semestre',
        categoria: 'coordenador',
        tags: [
            'retroativo',
            'reunião',
            'oficina',
            'visita atrasada',
            'presença',
            'ajuste',
        ],
        passos: [
            {
                passo: 1,
                titulo: 'Cadastrar Atividade Retroativa',
                descricao:
                    'Ao criar uma Visita, Reunião ou Oficina realizada em um mês passado (ex: Julho), selecione a data real da atividade. O sistema permite cadastros retroativos.',
            },
            {
                passo: 2,
                titulo: 'Prazo de Relatórios em Visitas Retroativas',
                descricao:
                    'Em visitas cadastradas após a sua realização, o prazo de 48h para envio do relatório conta a partir da data e hora do cadastro da visita no sistema.',
                dica: 'Se um relatório atrasado for aceito por um Administrador via Ajuste Administrativo, a pendência é 100% zerada e a visita é validada.',
            },
            {
                passo: 3,
                titulo: 'Lançar Inscrição e Presença via Ajustes',
                descricao:
                    'Em reuniões e oficinas retroativas (sem inscrição prévia online), acesse o evento > "Ajustes de Participação", selecione o voluntário e efetue a "Correção de Inscrição" e a "Correção de Presença" como Presente.',
            },
        ],
    },
    {
        id: 'adm-dashboard-status',
        titulo: 'Como interpretar os Status do Dashboard de Participantes',
        subtitulo:
            'Entendendo as situações: Dentro da Meta, Atenção, Requer Análise e Isento',
        categoria: 'coordenador',
        tags: [
            'dashboard',
            'status',
            'situação',
            'requer análise',
            'atenção',
            'dentro da meta',
            'isento',
        ],
        linkAcao: {
            label: 'Ver Dashboard',
            href: '/dashboards/visitas-por-participante',
        },
        passos: [
            {
                passo: 1,
                titulo: '🟢 Dentro da Meta',
                descricao:
                    'Voluntário em dia com as metas de visitas hospitalares do semestre e com pelo menos 50% de presença em Reuniões e Oficinas.',
            },
            {
                passo: 2,
                titulo: '🟡 Atenção',
                descricao:
                    'Voluntário em dia com as visitas, mas aguardando a conclusão das visitas do mês em andamento, ou com presença em Reuniões/Oficinas do semestre abaixo de 50%.',
            },
            {
                passo: 3,
                titulo: '🔵 Compensação Pendente',
                descricao:
                    'Voluntário com saldo de visitas pendente a compensar dentro do mesmo semestre.',
            },
            {
                passo: 4,
                titulo: '🔴 Requer Análise',
                descricao:
                    'Voluntário com inatividade superior a 60 dias sem atividades documentadas ou com débito de visitas expirado e não compensado no mês subsequente do mesmo semestre.',
            },
            {
                passo: 5,
                titulo: '⚪ Isento',
                descricao:
                    'Integrantes com tag ou cargo de Apoio, isentos da obrigação de cumprir metas de visitas hospitalares.',
            },
        ],
    },

    // --- GUIA DE IMPLANTAÇÃO DE GRUPOS ---
    {
        id: 'imp-cronograma',
        titulo: 'Roteiro de Implantação nos 3 Grupos (Santa Maria ➡️ Pelotas ➡️ Porto Alegre)',
        subtitulo: 'Cronograma e fases da estratégia de rollout',
        categoria: 'implantacao',
        tags: [
            'implantação',
            'santa maria',
            'pelotas',
            'porto alegre',
            'grupos',
            'cidades',
        ],
        passos: [
            {
                passo: 1,
                titulo: 'Fase 1: Piloto em Santa Maria',
                descricao:
                    'Implantação inicial no grupo pioneiro. Cadastro de hospitais de SM, convite aos voluntários da cidade, validação dos fluxos de visita e emissão dos primeiros relatórios em PDF.',
                dica: 'Duração estimada: 2 a 3 semanas para consolidação.',
            },
            {
                passo: 2,
                titulo: 'Fase 2: Expansão para Pelotas',
                descricao:
                    'Após validação em SM, cadastrar a cidade de Pelotas, seus hospitais parceiros (ex: Hospital Espírita) e enviar convites para o grupo local.',
            },
            {
                passo: 3,
                titulo: 'Fase 3: Expansão para Porto Alegre',
                descricao:
                    'Finalizando com a capital, cadastrando a cidade de Porto Alegre/Canoas, seus hospitais e onboarding dos voluntários.',
            },
        ],
    },
    {
        id: 'imp-checklist',
        titulo: 'Checklist para Ativação de um Novo Grupo / Cidade',
        subtitulo:
            'Guia passo a passo para o Coordenador ativar uma nova cidade no sistema',
        categoria: 'implantacao',
        tags: [
            'checklist',
            'ativação',
            'passo a passo',
            'configuração',
            'cidade',
        ],
        passos: [
            {
                passo: 1,
                titulo: '1. Verificar Cadastro da Cidade',
                descricao:
                    'Garantir que a cidade (ex: Santa Maria) está cadastrada na tabela de cidades do sistema.',
            },
            {
                passo: 2,
                titulo: '2. Cadastrar Hospitais e Alas Parceiras',
                descricao:
                    'No menu "Hospitais", cadastrar os estabelecimentos onde o grupo atuará e suas respectivas alas/unidades.',
            },
            {
                passo: 3,
                titulo: '3. Nomear Coordenadores Locais',
                descricao:
                    'Atribuir perfil de Administrador/Coordenador aos líderes locais do grupo.',
            },
            {
                passo: 4,
                titulo: '4. Disparar Convites aos Voluntários',
                descricao:
                    'Acessar "Voluntários" e enviar convites para todos os voluntários ativos do grupo daquela cidade.',
            },
            {
                passo: 5,
                titulo: '5. Agendar Primeiras Atividades',
                descricao:
                    'Cadastrar a primeira reunião/oficina mensal e as primeiras visitas hospitalares no calendário.',
            },
        ],
    },
];

export default function Index() {
    const [busca, setBusca] = useState('');
    const [abaAtiva, setAbaAtiva] = useState<
        'todos' | 'voluntario' | 'coordenador' | 'implantacao'
    >('todos');
    const [itemAberto, setItemAberto] = useState<string | null>('vol-convite');

    const toggleAccordion = (id: string) => {
        setItemAberto(itemAberto === id ? null : id);
    };

    const tutoriaisFiltrados = tutoriais.filter((item) => {
        const correspondeAba =
            abaAtiva === 'todos' || item.categoria === abaAtiva;
        const termo = busca.toLowerCase().trim();
        if (!termo) return correspondeAba;

        const textoBusca =
            `${item.titulo} ${item.subtitulo} ${item.tags.join(' ')} ${item.passos.map((p) => p.titulo + ' ' + p.descricao).join(' ')}`.toLowerCase();
        return correspondeAba && textoBusca.includes(termo);
    });

    return (
        <PainelLayout>
            <Head title="Central de Ajuda e Tutoriais" />

            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {/* Hero Header */}
                <div className="relative mb-8 overflow-hidden rounded-3xl border border-amber-200/80 bg-gradient-to-br from-amber-500 via-amber-400 to-yellow-400 p-6 text-white shadow-md sm:p-10">
                    <div className="relative z-10 max-w-3xl">
                        <div className="mb-3 inline-flex items-center gap-2 rounded-full bg-white/20 px-3.5 py-1 text-xs font-semibold tracking-wider text-amber-950 uppercase backdrop-blur-md">
                            <Sparkles className="size-4" />
                            Base de Conhecimento
                        </div>
                        <h1 className="text-3xl font-extrabold tracking-tight text-amber-950 sm:text-4xl">
                            Central de Ajuda & Tutoriais
                        </h1>
                        <p className="mt-2 text-base leading-relaxed font-medium text-amber-900 sm:text-lg">
                            Aprenda a utilizar todas as funcionalidades do
                            sistema do Esquadrão da Alegria. Passo a passo para
                            voluntários, coordenadores e implantação em novos
                            grupos.
                        </p>

                        {/* Barra de Busca */}
                        <div className="relative mt-6 max-w-xl">
                            <Search className="pointer-events-none absolute top-1/2 left-4 size-5 -translate-y-1/2 text-gray-400" />
                            <input
                                type="text"
                                value={busca}
                                onChange={(e) => setBusca(e.target.value)}
                                placeholder="Buscar por palavra-chave (ex: convite, relatório, visita, Santa Maria)..."
                                className="w-full rounded-2xl border-0 bg-white py-3.5 pr-4 pl-12 text-sm text-gray-900 shadow-lg ring-1 ring-black/5 placeholder:text-gray-400 focus:ring-2 focus:ring-amber-800"
                            />
                            {busca && (
                                <button
                                    onClick={() => setBusca('')}
                                    className="absolute top-1/2 right-3 -translate-y-1/2 rounded-full bg-gray-100 p-1 text-xs font-bold text-gray-500 hover:bg-gray-200"
                                >
                                    ✕
                                </button>
                            )}
                        </div>
                    </div>
                    {/* Elemento Decorativo */}
                    <CircleHelp className="pointer-events-none absolute -right-8 -bottom-8 size-64 text-amber-950/10" />
                </div>

                {/* Filtros por Categoria / Abas */}
                <div className="mb-6 flex flex-wrap items-center gap-2 border-b border-gray-200 pb-4">
                    <button
                        onClick={() => setAbaAtiva('todos')}
                        className={`flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition ${
                            abaAtiva === 'todos'
                                ? 'bg-amber-500 text-white shadow-sm'
                                : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                        }`}
                    >
                        <BookOpen className="size-4" />
                        Todos os Tutoriais ({tutoriais.length})
                    </button>

                    <button
                        onClick={() => setAbaAtiva('voluntario')}
                        className={`flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition ${
                            abaAtiva === 'voluntario'
                                ? 'bg-blue-600 text-white shadow-sm'
                                : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                        }`}
                    >
                        <Users className="size-4" />
                        🙋‍♂️ Manual do Voluntário
                    </button>

                    <button
                        onClick={() => setAbaAtiva('coordenador')}
                        className={`flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition ${
                            abaAtiva === 'coordenador'
                                ? 'bg-purple-600 text-white shadow-sm'
                                : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                        }`}
                    >
                        <Shield className="size-4" />
                        👑 Manual do Coordenador
                    </button>

                    <button
                        onClick={() => setAbaAtiva('implantacao')}
                        className={`flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition ${
                            abaAtiva === 'implantacao'
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 hover:text-gray-900'
                        }`}
                    >
                        <MapPin className="size-4" />
                        🚀 Implantação de Grupos
                    </button>
                </div>

                {/* Lista de Tutoriais (Accordions) */}
                <div className="space-y-4">
                    {tutoriaisFiltrados.length === 0 ? (
                        <div className="rounded-2xl border border-gray-200 bg-white p-12 text-center shadow-sm">
                            <Info className="mx-auto mb-3 size-12 text-amber-500" />
                            <h3 className="text-lg font-bold text-gray-900">
                                Nenhum tutorial encontrado
                            </h3>
                            <p className="mt-1 text-sm text-gray-500">
                                Não encontramos resultados para "{busca}". Tente
                                buscar por outros termos como "visita",
                                "convite", "oficina" ou selecione outra aba.
                            </p>
                            <button
                                onClick={() => {
                                    setBusca('');
                                    setAbaAtiva('todos');
                                }}
                                className="mt-4 inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600"
                            >
                                Limpar filtros
                            </button>
                        </div>
                    ) : (
                        tutoriaisFiltrados.map((item) => {
                            const aberto = itemAberto === item.id;
                            const badgeColor =
                                item.categoria === 'voluntario'
                                    ? 'bg-blue-100 text-blue-800 border-blue-200'
                                    : item.categoria === 'coordenador'
                                      ? 'bg-purple-100 text-purple-800 border-purple-200'
                                      : 'bg-emerald-100 text-emerald-800 border-emerald-200';

                            const categoriaNome =
                                item.categoria === 'voluntario'
                                    ? 'Voluntário'
                                    : item.categoria === 'coordenador'
                                      ? 'Coordenador / Admin'
                                      : 'Implantação';

                            return (
                                <div
                                    key={item.id}
                                    className={`overflow-hidden rounded-2xl border bg-white shadow-sm transition-all ${
                                        aberto
                                            ? 'border-amber-400 ring-1 ring-amber-400/30'
                                            : 'border-gray-200 hover:border-gray-300'
                                    }`}
                                >
                                    {/* Cabeçalho do Accordion */}
                                    <button
                                        onClick={() => toggleAccordion(item.id)}
                                        className="flex w-full items-center justify-between gap-4 p-5 text-left transition hover:bg-gray-50/80"
                                    >
                                        <div className="flex flex-1 flex-col gap-1.5 sm:flex-row sm:items-center sm:gap-3">
                                            <span
                                                className={`inline-flex w-fit items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold ${badgeColor}`}
                                            >
                                                {categoriaNome}
                                            </span>
                                            <div>
                                                <h2 className="text-base font-bold text-gray-900 sm:text-lg">
                                                    {item.titulo}
                                                </h2>
                                                <p className="text-xs text-gray-500 sm:text-sm">
                                                    {item.subtitulo}
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            {item.linkAcao && (
                                                <Link
                                                    href={item.linkAcao.href}
                                                    onClick={(e) =>
                                                        e.stopPropagation()
                                                    }
                                                    className="hidden items-center gap-1 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 sm:inline-flex"
                                                >
                                                    {item.linkAcao.label}
                                                    <ChevronRight className="size-3.5" />
                                                </Link>
                                            )}
                                            <div
                                                className={`rounded-full p-2 text-gray-400 transition-transform ${aberto ? 'rotate-180 bg-amber-100 text-amber-800' : 'bg-gray-100'}`}
                                            >
                                                <ChevronDown className="size-5" />
                                            </div>
                                        </div>
                                    </button>

                                    {/* Conteúdo Expandido do Passo a Passo */}
                                    {aberto && (
                                        <div className="border-t border-gray-100 bg-slate-50/50 p-5 sm:p-6">
                                            <div className="space-y-6">
                                                {item.passos.map((passo) => (
                                                    <div
                                                        key={passo.passo}
                                                        className="flex gap-4"
                                                    >
                                                        {/* Número do Passo */}
                                                        <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-amber-500 text-sm font-bold text-white shadow-sm">
                                                            {passo.passo}
                                                        </div>
                                                        {/* Detalhe */}
                                                        <div className="flex-1 pt-1">
                                                            <h3 className="text-base font-bold text-gray-900">
                                                                {passo.titulo}
                                                            </h3>
                                                            <p className="mt-1 text-sm leading-relaxed text-gray-600">
                                                                {
                                                                    passo.descricao
                                                                }
                                                            </p>
                                                            {passo.dica && (
                                                                <div className="mt-3 flex items-start gap-2 rounded-xl border border-amber-200/80 bg-amber-50 p-3 text-xs text-amber-900">
                                                                    <Sparkles className="mt-0.5 size-4 shrink-0 text-amber-600" />
                                                                    <div>
                                                                        <strong className="font-semibold text-amber-950">
                                                                            Dica:{' '}
                                                                        </strong>
                                                                        {
                                                                            passo.dica
                                                                        }
                                                                    </div>
                                                                </div>
                                                            )}
                                                        </div>
                                                    </div>
                                                ))}
                                            </div>

                                            {item.linkAcao && (
                                                <div className="mt-6 flex justify-end border-t border-gray-200/80 pt-4">
                                                    <Link
                                                        href={
                                                            item.linkAcao.href
                                                        }
                                                        className="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600"
                                                    >
                                                        {item.linkAcao.label}
                                                        <ChevronRight className="size-4" />
                                                    </Link>
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            );
                        })
                    )}
                </div>

                {/* Bloco de Suporte / Dúvidas */}
                <div className="mt-12 rounded-3xl border border-gray-200 bg-white p-6 text-center shadow-sm sm:p-8">
                    <HeartHandshake className="mx-auto mb-3 size-12 text-amber-500" />
                    <h3 className="text-xl font-bold text-gray-900">
                        Ainda tem dúvidas ou precisa de ajuda?
                    </h3>
                    <p className="mx-auto mt-2 max-w-xl text-sm text-gray-600">
                        Se você tiver alguma dúvida que não foi abordada neste
                        manual ou precisar de suporte com seu acesso, entre em
                        contato com a coordenação do seu grupo.
                    </p>
                    <div className="mt-5 flex justify-center gap-4">
                        <Link
                            href="/fale-conosco"
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
                        >
                            <Mail className="size-4" />
                            Fale Conosco
                        </Link>
                    </div>
                </div>
            </div>
        </PainelLayout>
    );
}
