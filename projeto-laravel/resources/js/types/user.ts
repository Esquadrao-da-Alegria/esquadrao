export interface User {
    id: number;
    name: string;
    email: string;
    active: boolean;
    profile_visibility: string;
    roles: {
        id: number;
        name: string;
        guard_name: string;
    }[];
}