import { FlashMessages } from '@/components/flash-messages';
import { PageHeader } from '@/components/page-header';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { useAuthorization } from '@/lib/authorization';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem, SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Boxes, DollarSign, Flower2, Package, Tags, Users } from 'lucide-react';

export default function Dashboard() {
    const { t } = useTranslations();
    const { can, hasModule } = useAuthorization();
    const { auth, company } = usePage<SharedData>().props;

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('nav.dashboard'), href: route('dashboard') }];

    const shortcuts = [
        { title: t('nav.customers'), url: route('customers.index'), icon: Users, permission: 'customers.view', module: 'sales' },
        { title: t('nav.marks'), url: route('marks.index'), icon: Tags, permission: 'marks.view', module: 'sales' },
        { title: t('nav.prices'), url: route('prices.index'), icon: DollarSign, permission: 'product_prices.view', module: 'sales' },
        {
            title: t('nav.varieties'),
            url: route('varieties.index'),
            icon: Flower2,
            permission: 'flower_varieties.view',
            module: 'postharvest',
        },
        {
            title: t('nav.products'),
            url: route('products.index'),
            icon: Package,
            permission: 'products.view',
            module: 'postharvest',
        },
        {
            title: t('nav.box_types'),
            url: route('box-types.index'),
            icon: Boxes,
            permission: 'box_types.view',
            module: 'postharvest',
        },
    ].filter((shortcut) => can(shortcut.permission) && (shortcut.module === undefined || hasModule(shortcut.module)));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('nav.dashboard')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={company?.name ?? t('nav.dashboard')} description={auth.user?.name ?? undefined} />

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {shortcuts.map((shortcut) => (
                        <Link key={shortcut.url} href={shortcut.url}>
                            <Card className="transition-colors hover:border-primary/50 hover:bg-accent/40">
                                <CardContent className="flex items-center gap-3 p-5">
                                    <span className="flex h-10 w-10 items-center justify-center rounded-md bg-primary/10 text-primary">
                                        <shortcut.icon className="h-5 w-5" />
                                    </span>
                                    <span className="font-medium">{shortcut.title}</span>
                                </CardContent>
                            </Card>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
