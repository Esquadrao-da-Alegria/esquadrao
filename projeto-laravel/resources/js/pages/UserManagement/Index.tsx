import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Toggle } from '@/components/ui/toggle';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    active: boolean;
    role: {
        id: number;
        nomeRole: string;
    };
}

interface Role {
    id: number;
    nomeRole: string;
}

interface Props {
    users: User[];
    roles: Role[];
}

export default function UserManagement({ users, roles }: Props) {
    const [selectedRoles, setSelectedRoles] = useState<Record<number, number>>({});

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
        },
        {
            title: 'Gerenciamento de Usuários',
            href: '/user-management',
        },
    ];

    const handleRoleChange = (userId: number, roleId: number) => {
        setSelectedRoles(prev => ({ ...prev, [userId]: roleId }));
        
        router.post(`/user-management/${userId}/update-role`, {
            role_id: roleId
        });
    };

    const handleToggleActive = (userId: number) => {
        router.post(`/user-management/${userId}/toggle-active`);
    };

    const getRoleBadgeColor = (roleName: string) => {
        switch (roleName) {
            case 'admin':
                return 'bg-red-100 text-red-800';
            case 'diretor':
                return 'bg-purple-100 text-purple-800';
            case 'coordenador':
                return 'bg-blue-100 text-blue-800';
            case 'voluntario':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Gerenciamento de Usuários" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold text-gray-900">
                        Gerenciamento de Usuários
                    </h1>
                    <p className="text-gray-600 mt-2">
                        Gerencie roles e status dos usuários do sistema
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Usuários</CardTitle>
                    </CardHeader>
                    <CardContent>
                                    <div className="space-y-4">
                                        {users.map((user) => (
                                            <div
                                                key={user.id}
                                                className="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50"
                                            >
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-3">
                                                        <div>
                                                            <h3 className="font-semibold text-gray-900">
                                                                {user.name}
                                                            </h3>
                                                            <p className="text-sm text-gray-600">
                                                                {user.email}
                                                            </p>
                                                        </div>
                                                        <Badge className={getRoleBadgeColor(user.role.nomeRole)}>
                                                            {user.role.nomeRole}
                                                        </Badge>
                                                        <Badge 
                                                            variant={user.active ? "default" : "secondary"}
                                                            className={user.active ? "bg-green-100 text-green-800" : "bg-gray-100 text-gray-800"}
                                                        >
                                                            {user.active ? 'Ativo' : 'Inativo'}
                                                        </Badge>
                                                    </div>
                                                </div>

                                                <div className="flex items-center gap-4">
                                                    <div className="flex items-center gap-2">
                                                        <label className="text-sm font-medium">
                                                            Role:
                                                        </label>
                                                        <Select
                                                            value={user.role.id.toString()}
                                                            onValueChange={(value) => 
                                                                handleRoleChange(user.id, parseInt(value))
                                                            }
                                                        >
                                                            <SelectTrigger className="w-40">
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                {roles.map((role) => (
                                                                    <SelectItem 
                                                                        key={role.id} 
                                                                        value={role.id.toString()}
                                                                    >
                                                                        {role.nomeRole}
                                                                    </SelectItem>
                                                                ))}
                                                            </SelectContent>
                                                        </Select>
                                                    </div>

                                                    <div className="flex items-center gap-2">
                                                        <label className="text-sm font-medium">
                                                            Ativo:
                                                        </label>
                                                        <Toggle
                                                            pressed={user.active}
                                                            onPressedChange={() => handleToggleActive(user.id)}
                                                            className={user.active ? "bg-green-500 text-white" : "bg-gray-300"}
                                                        >
                                                            {user.active ? '✓' : '✗'}
                                                        </Toggle>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    {users.length === 0 && (
                                        <div className="text-center py-8 text-gray-500">
                                            Nenhum usuário encontrado
                                        </div>
                                    )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
