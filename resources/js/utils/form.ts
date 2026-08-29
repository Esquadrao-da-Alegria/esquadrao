function obterCookie(nome: string): string | null {
    const valor = `; ${document.cookie}`;
    const partes = valor.split(`; ${nome}=`);
    if (partes.length === 2) {
        return decodeURIComponent(partes.pop()?.split(';').shift() ?? '');
    }
    return null;
}

export function obterCsrfToken(): string {
    return (
        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
            ?.content ?? ''
    );
}

export function obterCsrfHeaders(): Record<string, string> {
    const xsrfCookie = obterCookie('XSRF-TOKEN');
    if (xsrfCookie) {
        return {
            'X-XSRF-TOKEN': xsrfCookie,
        };
    }

    const csrfMeta = obterCsrfToken();
    if (csrfMeta) {
        return {
            'X-CSRF-TOKEN': csrfMeta,
        };
    }

    return {};
}
