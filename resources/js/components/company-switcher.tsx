import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { useTranslations } from '@/lib/i18n';
import type { SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { Building2, Check, ChevronsUpDown } from 'lucide-react';

/**
 * Muestra la empresa activa y, si el usuario trabaja en varias fincas, deja
 * cambiar de empresa.
 */
export function CompanySwitcher() {
    const { company, companies } = usePage<SharedData>().props;
    const { t } = useTranslations();

    if (company === null) {
        return null;
    }

    const logo = company.logo_url ? (
        <img src={company.logo_url} alt={company.name} className="h-6 w-6 rounded object-cover" />
    ) : (
        <span className="flex h-6 w-6 items-center justify-center rounded bg-sidebar-primary text-sidebar-primary-foreground">
            <Building2 className="h-3.5 w-3.5" />
        </span>
    );

    if (companies.length <= 1) {
        return (
            <div className="flex items-center gap-2 px-2 py-1.5 text-sm font-medium">
                {logo}
                <span className="truncate">{company.name}</span>
            </div>
        );
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="sm" className="w-full justify-start gap-2">
                    {logo}
                    <span className="truncate font-medium">{company.name}</span>
                    <ChevronsUpDown className="ml-auto h-4 w-4 opacity-60" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" className="w-56">
                <DropdownMenuLabel>{t('common.company')}</DropdownMenuLabel>
                {companies.map((option) => (
                    <DropdownMenuItem
                        key={option.id}
                        className="justify-between"
                        onClick={() => router.put(route('current-company.update', option.id))}
                    >
                        <span className="truncate">{option.name}</span>
                        {option.id === company.id && <Check className="h-4 w-4" />}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
