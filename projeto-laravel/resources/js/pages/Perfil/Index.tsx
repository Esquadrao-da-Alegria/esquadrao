import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import type { User } from '@/types/user';
import { Pencil } from "lucide-react";

interface Props {
    user: User; 
    role: string;
}

export default function Perfil({ user, role }: Props) {
    return (
        <AppLayout>
            <Head title="Meu Perfil" />

            <div className="relative flex flex-col gap-8 p-6 py-10 max-w-4xl mx-auto w-full">
                <h1 className="text-3xl font-bold text-gray-900">
                    Meu Perfil
                </h1>

                <div className="space-y-6">
                    <div className="rounded-2xl border bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300">


                        <div className="flex items-center gap-4 mb-6">
                            <div className="rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white h-14 w-14 flex items-center justify-center shadow-md text-xl font-bold">
                                {user.name.charAt(0).toUpperCase()}
                            </div>

                            <div>
                                <h3 className="font-semibold text-gray-900 text-xl">
                                    {user.name}
                                </h3>
                                <p className="text-sm text-gray-600">{user.email}</p>
                            </div>
                        </div>

                        {/* Campos de perfil */}
                        <div className="space-y-4">

                            {/* Nome */}
                            <div className="flex justify-between items-center">
                                <div>
                                    <p className="text-sm text-gray-500">Nome</p>
                                    <p className="text-gray-900 font-medium">{user.name}</p>
                                </div>

                                {/* Ícone de editar */}
                                <button
                                    className="p-2 rounded-lg hover:bg-gray-100 transition"
                                    title="Editar nome"
                                >
                                    <Pencil size={18} className="text-gray-600" />
                                </button>
                            </div>

                            {/* Email */}
                            <div>
                                <p className="text-sm text-gray-500">Email</p>
                                <p className="text-gray-900 font-medium">{user.email}</p>
                            </div>

                            {/* Cargo */}
                            <div>
                                <p className="text-sm text-gray-500">Cargo</p>
                                <p className="text-gray-900 font-medium">
                                    {role ?? "Sem cargo"}
                                </p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}