import { UserPlus } from 'lucide-react';

interface Props {
    ehAdministrador: boolean;
    onCadastrar: () => void;
}

const Header: React.FC<Props> = ({ ehAdministrador, onCadastrar }) => {
    return (
        <header className="mb-10 flex flex-col gap-4 sm:mb-12 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 className="text-2xl font-semibold tracking-tight text-amber-950 sm:text-3xl">
                    Voluntários
                </h1>
                <p className="mt-1 max-w-md text-sm text-amber-900/55">
                    Gerencie convites e acompanhe o status de cada voluntário
                </p>
            </div>
            {ehAdministrador ? (
                <button
                    onClick={onCadastrar}
                    type="button"
                    className="inline-flex w-full shrink-0 items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 text-sm font-semibold text-amber-700 shadow-sm transition hover:bg-amber-50 focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 focus:outline-none sm:w-auto"
                >
                    <UserPlus className="size-5" strokeWidth={2} aria-hidden />
                    Convidar voluntário
                </button>
            ) : null}
        </header>
    );
};

export default Header;
