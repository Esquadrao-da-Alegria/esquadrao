export interface User {
    id: number;
    name: string;
    email: string;
    active: boolean;
    roles: {
        id: number;
        name: string;
        guard_name: string;
    }[];
}