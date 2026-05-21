import { User } from '@/types';

export const temCargo = (user: User, cargoSlug: string): boolean => {
    return user.cargos?.some((cargo) => cargo.slug === cargoSlug) ?? false;
};
