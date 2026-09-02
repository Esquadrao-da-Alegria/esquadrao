import { type FC } from 'react';

const itens = [
    { cor: 'bg-sky-500', texto: '1 inscrito ou sem limite' },
    { cor: 'bg-orange-500', texto: 'Ainda há vagas' },
    { cor: 'bg-red-600', texto: 'Visita cheia' },
    { cor: 'bg-purple-600', texto: 'Ação especial' },
    { cor: 'bg-indigo-600', texto: 'Evento' },
    { cor: 'bg-gray-400', texto: 'Cancelada' },
    { cor: 'bg-green-200', texto: 'Concluída/contabilizada' },
];

const Show: FC = () => (
    <div className="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 px-1 text-xs text-amber-900/65" aria-label="Legenda do calendário">
        {itens.map((item) => (
            <span key={item.texto} className="inline-flex items-center gap-1.5">
                <span className={`size-2.5 rounded-full ${item.cor}`} aria-hidden />
                {item.texto}
            </span>
        ))}
    </div>
);

export default Show;
