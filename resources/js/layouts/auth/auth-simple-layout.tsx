import { home } from '@/routes';
import { Link } from '@inertiajs/react';
import { type PropsWithChildren } from 'react';

interface AuthLayoutProps {
    name?: string;
    title?: string;
    description?: string;
}

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: PropsWithChildren<AuthLayoutProps>) {
    return (
        <div className="flex min-h-svh flex-col bg-muted/35 px-4 py-10 sm:px-6 sm:py-12">
            <div className="mx-auto flex w-full max-w-[420px] flex-1 flex-col justify-center">
                <div className="flex flex-col gap-8">
                    <header className="flex flex-col items-center gap-4 text-center sm:gap-5">
                        <Link
                            href={home()}
                            className="block transition-opacity hover:opacity-90"
                        >
                            <img
                                src="/assets/images/logo-colorida.png"
                                alt="Esquadrão da Alegria"
                                className="mx-auto h-16 w-auto max-w-[min(100%,280px)] object-contain sm:h-20"
                                width={280}
                                height={80}
                                decoding="async"
                            />
                            <span className="sr-only">Voltar ao início</span>
                        </Link>

                        <div className="space-y-1.5 sm:space-y-2">
                            <h1 className="text-balance text-2xl font-semibold tracking-tight text-foreground sm:text-[1.65rem]">
                                {title}
                            </h1>
                            <p className="text-pretty text-sm leading-relaxed text-muted-foreground sm:text-[0.9375rem]">
                                {description}
                            </p>
                        </div>
                    </header>

                    <div className="rounded-xl border border-border/60 bg-card text-card-foreground shadow-sm sm:rounded-2xl">
                        <div className="p-6 sm:p-8">{children}</div>
                    </div>
                </div>
            </div>
        </div>
    );
}
