import { LucideIcon } from 'lucide-react';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    locale: string | null;
    roles: string[];
    permissions: string[];
}

export interface Auth {
    user: AuthUser | null;
}

export interface CompanySummary {
    id: number;
    name: string;
    logo_url?: string | null;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

/** Árbol de traducciones tal como viaja desde lang/<locale>. */
export type TranslationTree = { [key: string]: string | TranslationTree };

export interface SharedData {
    name: string;
    locale: string;
    locales: string[];
    translations: TranslationTree;
    auth: Auth;
    company: CompanySummary | null;
    companies: CompanySummary[];
    modules: string[];
    flash: { success: string | null; error: string | null };
    [key: string]: unknown;
}

/** Página de resultados de un paginador de Laravel. */
export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

/** Estado de búsqueda, orden y paginación que mantiene el servidor. */
export interface TableState {
    search: string;
    sort: string;
    direction: 'asc' | 'desc';
    per_page: number;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}
