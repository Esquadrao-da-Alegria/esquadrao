import { AbaKey, StatusCounters } from './types';

interface Props {
    aba: AbaKey;
    contadores: StatusCounters;
    onAbaChange: (aba: AbaKey) => void;
}

const abas: Array<{ key: AbaKey; label: string }> = [
    { key: 'voluntarios', label: 'Voluntários' },
    { key: 'convidados', label: 'Convidados' },
];

const Abas: React.FC<Props> = ({ aba, contadores, onAbaChange }) => {
    return (
        <div className="mb-6 inline-flex rounded-2xl border border-gray-200 bg-white p-1 shadow-sm">
            {abas.map((item) => (
                <button
                    key={item.key}
                    type="button"
                    onClick={() => onAbaChange(item.key)}
                    className={`inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition ${
                        aba === item.key
                            ? 'bg-amber-50 text-amber-800 ring-1 ring-amber-100'
                            : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900'
                    }`}
                >
                    {item.label}
                    <span className="rounded-full bg-white px-2 py-0.5 text-xs text-gray-500 ring-1 ring-gray-100">
                        {contadores[item.key] ?? 0}
                    </span>
                </button>
            ))}
        </div>
    );
};

export default Abas;
