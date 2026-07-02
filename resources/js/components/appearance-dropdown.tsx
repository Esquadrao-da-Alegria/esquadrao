import { Button } from '@/components/ui/button';
import { Sun } from 'lucide-react';
import { HTMLAttributes } from 'react';

/** Tema claro fixo; sem alternância. */
export default function AppearanceToggleDropdown({
    className = '',
    ...props
}: HTMLAttributes<HTMLDivElement>) {
    return (
        <div className={className} {...props}>
            <Button
                type="button"
                variant="ghost"
                size="icon"
                className="h-9 w-9 rounded-md"
                disabled
                aria-label="Tema claro (único disponível)"
            >
                <Sun className="h-5 w-5" />
            </Button>
        </div>
    );
}
