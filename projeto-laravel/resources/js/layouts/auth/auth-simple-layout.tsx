// auth-simple-layout.tsx
import React from 'react';

export default function AuthLayoutTemplate({
    children,
    title,
    description,
}: {
    children: React.ReactNode;
    title: string;
    description: string;
}) {
    return (
        <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-yellow-100 via-pink-100 to-purple-100 p-6">
            <div className="relative w-full max-w-md overflow-hidden rounded-3xl bg-white shadow-2xl p-8 md:p-12">
                {/* Elementos decorativos */}
                <div className="absolute -top-6 -left-6 h-16 w-16 animate-pulse rounded-full bg-yellow-300 opacity-40"></div>
                <div className="absolute -bottom-6 -right-6 h-12 w-12 animate-bounce rounded-full bg-pink-300 opacity-30" style={{ animationDelay: '0.5s' }}></div>

                {/* Título e descrição */}
                <div className="mb-8 text-center">
                    <h1 className="text-3xl font-extrabold text-gray-900 md:text-4xl">{title}</h1>
                    <p className="mt-2 text-gray-600">{description}</p>
                </div>

                {/* Conteúdo do formulário */}
                {children}

                {/* Footer opcional */}
                <div className="mt-6 text-center text-sm text-gray-500">
                    © {new Date().getFullYear()} Esquadrão da Alegria
                </div>
            </div>
        </div>
    );
}
