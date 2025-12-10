import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Toggle } from '@/components/ui/toggle';
import type { BreadcrumbItem } from '@/types';
import type { User } from '@/types/user';
import type { Role } from '@/types/role';


interface Props {
    users: User[];
    roles: Role[];
}

export default function UserManagement({ users, roles }: Props) {
    const [selectedRoles, setSelectedRoles] = useState<Record<number, number>>({});

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Gerenciamento de Voluntários', href: '/user-management' },
    ];

    const handleRoleChange = (userId: number, roleId: number) => {
        setSelectedRoles(prev => ({ ...prev, [userId]: roleId }));
        router.post(`/user-management/${userId}/update-role`, { role_id: roleId });
    };

    const handleToggleActive = (userId: number) => {
        router.post(`/user-management/${userId}/toggle-active`);
    };

    return (
        <AppLayout>
            <Head title="Gerenciamento de Voluntários" />

            <div className="relative flex flex-col gap-8 p-6 py-10 max-w-5xl mx-auto w-full">


                <div className="absolute -top-6 -left-6 h-10 w-10 rounded-full bg-blue-300 opacity-30 animate-pulse"></div>
                <div className="absolute -bottom-6 -right-6 h-8 w-8 rounded-full bg-purple-300 opacity-30 animate-bounce"></div>

                <div>
                    <h1 className="text-3xl font-bold text-gray-900">
                        Gerenciamento de Voluntários
                    </h1>
                    <p className="text-gray-600 mt-1">
                        Controle roles e status dos voluntários do Esquadrão da Alegria
                    </p>
                </div>

                
                <div className="space-y-6">

                    {users.map((user) => (
                        <div
                            key={user.id}
                            className="rounded-2xl border bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300"
                        >
                            <div className="flex flex-col gap-5 md:flex-row md:justify-between">
                                
                           
                                <div className="flex items-start gap-4">
                                    <div className="rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white h-12 w-12 flex items-center justify-center shadow-md text-lg font-bold">
                                        {user.name.charAt(0).toUpperCase()}
                                    </div>

                                    <div>
                                        <h3 className="font-semibold text-gray-900 text-lg">
                                            {user.name}
                                        </h3>
                                        <p className="text-sm text-gray-600">{user.email}</p>

                                        <div className="mt-2 flex gap-2 flex-wrap">
                                            <span className="px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs">
                                                {user.roles[0]?.name ?? "Sem cargo"}
                                            </span>

                                            <span
                                                className={`px-3 py-1 rounded-full text-xs ${
                                                    user.active
                                                        ? "bg-green-100 text-green-700"
                                                        : "bg-gray-200 text-gray-700"
                                                }`}
                                            >
                                                {user.active ? "Ativo" : "Inativo"}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {/* Right side - Controls */}
                                <div className="flex flex-col gap-5 md:items-end">

                                    {/* Select Role */}
                                    <div className="flex flex-col gap-2">
                                        <label className="text-sm font-medium">Cargo</label>
                                        <Select
                                            value={user.roles[0]?.id?.toString() ?? ""}
                                            onValueChange={(value) =>
                                                handleRoleChange(user.id, parseInt(value))
                                            }
                                        >
                                            <SelectTrigger className="w-48 rounded-xl border-gray-300 shadow-sm">
                                                <SelectValue placeholder="Selecionar" />
                                            </SelectTrigger>

                                            <SelectContent>
                                                {roles.map(role => (
                                                    <SelectItem key={role.id} value={role.id.toString()}>
                                                        {role.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    {/* Toggle Active */}
                                    <div className="flex flex-col gap-2">
                                        <label className="text-sm font-medium">Status</label>

                                        <Toggle
                                            pressed={user.active}
                                            onPressedChange={() => handleToggleActive(user.id)}
                                            className={`rounded-xl px-4 py-2 text-white font-medium shadow-md transition-all ${
                                                user.active
                                                    ? "bg-green-500 hover:bg-green-600"
                                                    : "bg-gray-400 hover:bg-gray-500"
                                            }`}
                                        >
                                            {user.active ? "Ativo" : "Inativo"}
                                        </Toggle>
                                    </div>

                                </div>
                            </div>
                        </div>
                    ))}

                    {users.length === 0 && (
                        <div className="text-center py-10 text-gray-500">
                            Nenhum usuário encontrado
                        </div>
                    )}
                </div>        
            </div>
        </AppLayout>
    );
}
