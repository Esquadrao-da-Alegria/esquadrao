import { cn } from '@/lib/utils';
import { Sun } from 'lucide-react';
import { HTMLAttributes } from 'react';

/** Indicador estático: apenas tema claro está disponível. */
export default function AppearanceToggleTab({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div
            className={cn(
                'inline-flex gap-1 rounded-lg bg-neutral-100 p-1',
                className,
            )}
            {...props}
        >
            <div className="flex items-center rounded-md bg-white px-3.5 py-1.5 shadow-xs">
                <Sun className="-ml-1 h-4 w-4" />
                <span className="ml-1.5 text-sm">Claro</span>
            </div>
        </div>
    );
}
