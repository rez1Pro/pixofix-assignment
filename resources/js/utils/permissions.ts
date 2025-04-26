import { usePage } from '@inertiajs/vue3';

export const hasPermission = (permission: string): boolean => {
    if (permission === 'any') {
        return true;
    }
    const permissions = usePage().props.auth.permissions as string[];
    return permissions.includes(permission);
};