import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useTranslations } from '@/lib/i18n';
import type { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { Check, Languages } from 'lucide-react';

const LANGUAGE_NAMES: Record<string, string> = {
    es: 'Español',
    en: 'English',
};

/** Cambia el idioma de la interfaz y lo recuerda en el usuario. */
export function LanguageSwitcher() {
    const { locale, locales } = usePage<SharedData>().props;
    const { t } = useTranslations();

    const change = (next: string) => {
        if (next === locale) {
            return;
        }

        router.post(route('locale.store'), { locale: next }, { preserveScroll: true });
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="sm" className="w-full justify-start gap-2" aria-label={t('common.language')}>
                    <Languages className="h-4 w-4" />
                    <span className="truncate">{LANGUAGE_NAMES[locale] ?? locale}</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="w-44">
                {locales.map((option) => (
                    <DropdownMenuItem key={option} onClick={() => change(option)} className="justify-between">
                        {LANGUAGE_NAMES[option] ?? option}
                        {option === locale && <Check className="h-4 w-4" />}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
