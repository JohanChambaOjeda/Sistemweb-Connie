import type { SharedData, TranslationTree } from '@/types';
import { usePage } from '@inertiajs/react';

/**
 * Busca una clave con puntos ("customers.fields.name") en el árbol de
 * traducciones y reemplaza los :parametros de Laravel.
 */
export function translate(tree: TranslationTree, key: string, replacements: Record<string, string | number> = {}): string {
    const value = key.split('.').reduce<string | TranslationTree | undefined>((carry, part) => {
        if (carry === undefined || typeof carry === 'string') {
            return undefined;
        }

        return carry[part];
    }, tree);

    if (typeof value !== 'string') {
        // Si falta la traducción se muestra la clave: así se detecta en pantalla.
        return key;
    }

    return Object.entries(replacements).reduce(
        (text, [name, replacement]) => text.replaceAll(`:${name}`, String(replacement)),
        value,
    );
}

export function useTranslations() {
    const { translations, locale } = usePage<SharedData>().props;

    return {
        locale,
        t: (key: string, replacements: Record<string, string | number> = {}) => translate(translations, key, replacements),
    };
}
