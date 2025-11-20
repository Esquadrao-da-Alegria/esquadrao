import { usePage } from "@inertiajs/react";
import type { PropsWithChildren } from "react";

interface Props extends PropsWithChildren {
    role: string | string[];
}

export default function AuthRole({ role, children }: Props) {
    const { props } = usePage();
    const user = (props as any)?.auth?.user;
    console.log("USER ROLES:", user?.roles);

    if (!user) return null;

    const roles = user.roles?.map((r: any) => r.name) ?? [];

    const required = Array.isArray(role) ? role : [role];

    const hasRole = required.some(r => roles.includes(r));

    if (!hasRole) return null;
    
    return <>{children}</>;
    
}
