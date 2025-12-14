
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/AppLayout';
import type { BreadcrumbItem } from '@/types';
import type { Doutor } from '@/types/doutor';
import type { User } from '@/types/user';



interface Props {
    doutores: Doutor[];
    users: User[];
}

export default function UserManagement({doutores} : Props) {

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Doutores', href: '/doutores' },
        { title: 'Gerenciamento de Doutores', href: '/doutores' },
    ];

    return (
        <AppLayout>
            <Head title="Nossos doutores" />

            <div className="relative flex flex-col gap-8 p-6 py-10 max-w-5xl mx-auto w-full">


                <div className="absolute -top-6 -left-6 h-10 w-10 rounded-full bg-blue-300 opacity-30 animate-pulse"></div>
                <div className="absolute -bottom-6 -right-6 h-8 w-8 rounded-full bg-purple-300 opacity-30 animate-bounce"></div>

                <div>
                    <h1 className="text-3xl font-bold text-gray-900">
                        Nossos doutores
                    </h1>
                </div>

                
                <div className="space-y-6">

                    {doutores.map((doutor) => (
                            
                            <div
                                key={doutor.id}
                                className="rounded-2xl border bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300"
                            >
                                <div className="flex flex-col gap-5 md:flex-row md:justify-between">
                                    
                            
                                    <div className="flex items-start gap-4">

                                        <div>
                                            <h3 className="font-semibold text-gray-900 text-lg">
                                                {doutor.nome}
                                            </h3>
                                            <p className="text-sm text-gray-600">{doutor.descricao}</p>

                                        </div>
                                    </div>

                                    </div>
                                </div>
                        ))}

                        {doutores.length === 0 && (
                            <div className="text-center py-10 text-gray-500">
                                Nenhum doutor encontrado
                            </div>
                        )}
                </div>        
            </div>
        </AppLayout>
    );
}
