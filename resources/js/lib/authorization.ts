import type { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

/**
 * Permisos del usuario y módulos contratados por la empresa activa, tal como
 * los comparte Inertia. Sirve para esconder opciones del menú y botones.
 */
export function useAuthorization() {
    const { auth, modules } = usePage<SharedData>().props;

    const permissions = auth.user?.permissions ?? [];
    const roles = auth.user?.roles ?? [];

    return {
        can: (permission: string) => permissions.includes(permission),
        canAny: (...wanted: string[]) => wanted.some((permission) => permissions.includes(permission)),
        hasRole: (role: string) => roles.includes(role),
        hasModule: (module: string) => modules.includes(module),
    };
}
