import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Search } from 'lucide-react';
import { statusOptions } from './status';
import { AbaKey, StatusFiltro } from './types';

interface Props {
    aba: AbaKey;
    busca: string;
    statusFiltro: StatusFiltro;
    onBuscaChange: (busca: string) => void;
    onStatusChange: (status: StatusFiltro) => void;
}

const Filtros: React.FC<Props> = ({
    aba,
    busca,
    statusFiltro,
    onBuscaChange,
    onStatusChange,
}) => {
    return (
        <section className="mt-6 flex flex-col gap-3 lg:flex-row">
            <div className="relative flex-1">
                <Search
                    className="pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2 text-gray-400"
                    aria-hidden
                />
                <input
                    type="search"
                    value={busca}
                    onChange={(event) => onBuscaChange(event.target.value)}
                    placeholder="Buscar por nome ou e-mail..."
                    className="h-11 w-full rounded-2xl border border-gray-200 bg-white pr-4 pl-11 text-sm text-gray-700 shadow-sm transition placeholder:text-gray-400 focus:border-amber-300 focus:ring-2 focus:ring-amber-100 focus:outline-none"
                />
            </div>
            {aba === 'convidados' ? (
                <div className="lg:w-52">
                    <Select value={statusFiltro} onValueChange={onStatusChange}>
                        <SelectTrigger className="h-11 rounded-2xl border-gray-200 bg-white px-4 shadow-sm focus:ring-amber-100">
                            <SelectValue placeholder="Todos os status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="todos">
                                Todos os status
                            </SelectItem>
                            {statusOptions.map((status) => (
                                <SelectItem key={status.key} value={status.key}>
                                    {status.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            ) : null}
        </section>
    );
};

export default Filtros;
