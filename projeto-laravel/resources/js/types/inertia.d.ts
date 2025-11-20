import '@inertiajs/react';
import { PageProps as InertiaPageProps } from '@inertiajs/core';

declare module '@inertiajs/react' {
    export interface PageProps extends InertiaPageProps {
        auth:{
           user: {
            id: number;
            name: string;
            email: string;
            avatar: string | null;
            } | null; 
        };
    }
}