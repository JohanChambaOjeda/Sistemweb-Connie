import { CompanySwitcher } from '@/components/company-switcher';
import { LanguageSwitcher } from '@/components/language-switcher';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useAuthorization } from '@/lib/authorization';
import { useTranslations } from '@/lib/i18n';
import { Link, usePage } from '@inertiajs/react';
import {
    Boxes,
    DollarSign,
    Flower2,
    LayoutGrid,
    Package,
    ScrollText,
    Upload,
    Ship,
    Tags,
    Users,
    UserCog,
    type LucideIcon,
} from 'lucide-react';

interface MenuEntry {
    title: string;
    url: string;
    icon: LucideIcon;
    permission?: string;
}

interface MenuGroup {
    title: string;
    module?: string;
    items: MenuEntry[];
}

/**
 * El menú se arma con los módulos del plan de la empresa y los permisos del
 * usuario: lo que no puede usar, no lo ve.
 */
export function AppSidebar() {
    const { t } = useTranslations();
    const { can, hasModule } = useAuthorization();
    const page = usePage();

    const groups: MenuGroup[] = [
        {
            title: t('nav.groups.main'),
            items: [{ title: t('nav.dashboard'), url: route('dashboard'), icon: LayoutGrid }],
        },
        {
            title: t('nav.groups.commercial'),
            module: 'sales',
            items: [
                { title: t('nav.customers'), url: route('customers.index'), icon: Users, permission: 'customers.view' },
                { title: t('nav.marks'), url: route('marks.index'), icon: Tags, permission: 'marks.view' },
                { title: t('nav.cargo_agencies'), url: route('cargo-agencies.index'), icon: Ship, permission: 'cargo_agencies.view' },
                { title: t('nav.prices'), url: route('prices.index'), icon: DollarSign, permission: 'product_prices.view' },
            ],
        },
        {
            title: t('nav.groups.catalog'),
            module: 'postharvest',
            items: [
                { title: t('nav.varieties'), url: route('varieties.index'), icon: Flower2, permission: 'flower_varieties.view' },
                { title: t('nav.products'), url: route('products.index'), icon: Package, permission: 'products.view' },
                { title: t('nav.box_types'), url: route('box-types.index'), icon: Boxes, permission: 'box_types.view' },
            ],
        },
        {
            title: t('nav.groups.admin'),
            items: [
                { title: t('nav.users'), url: route('users.index'), icon: UserCog, permission: 'users.view' },
                { title: t('nav.activity'), url: route('activity.index'), icon: ScrollText, permission: 'activity_log.view' },
                { title: t('nav.imports'), url: route('imports.index'), icon: Upload, permission: 'imports.run' },
            ],
        },
    ];

    const visibleGroups = groups
        .filter((group) => group.module === undefined || hasModule(group.module))
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => item.permission === undefined || can(item.permission)),
        }))
        .filter((group) => group.items.length > 0);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <CompanySwitcher />
            </SidebarHeader>

            <SidebarContent>
                {visibleGroups.map((group) => (
                    <SidebarGroup key={group.title} className="px-2 py-0">
                        <SidebarGroupLabel>{group.title}</SidebarGroupLabel>
                        <SidebarMenu>
                            {group.items.map((item) => (
                                <SidebarMenuItem key={item.url}>
                                    <SidebarMenuButton asChild isActive={page.url.startsWith(new URL(item.url, 'http://localhost').pathname)} tooltip={item.title}>
                                        <Link href={item.url} prefetch>
                                            <item.icon />
                                            <span>{item.title}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ))}
                        </SidebarMenu>
                    </SidebarGroup>
                ))}
            </SidebarContent>

            <SidebarFooter>
                <LanguageSwitcher />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
