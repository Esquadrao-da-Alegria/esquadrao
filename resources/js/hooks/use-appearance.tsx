import { useCallback, useEffect, useState } from 'react';

export type Appearance = 'light';

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

/** Aplica sempre tema claro e remove a classe `dark` do documento. */
export function applyLightTheme() {
    document.documentElement.classList.remove('dark');
    document.documentElement.style.colorScheme = 'light';
}

export function initializeTheme() {
    applyLightTheme();
    localStorage.setItem('appearance', 'light');
    setCookie('appearance', 'light');
}

export function useAppearance() {
    const [appearance] = useState<Appearance>('light');

    const updateAppearance = useCallback(() => {
        initializeTheme();
    }, []);

    useEffect(() => {
        initializeTheme();
    }, []);

    return { appearance, updateAppearance } as const;
}
