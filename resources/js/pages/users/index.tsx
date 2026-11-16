import { DataTable, type Column } from '@/components/data-table';
import { FlashMessages } from '@/components/flash-messages';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { useAuthorization } from '@/lib/authorization';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem, Paginated, TableState } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Power } from 'lucide-react';

interface UserRow extends Record<string, unknown> {
    id: number;
    name: string;
    email: string;
    locale: string | null;
    is_active: boolean;
    role: string | null;
}

export default function UsersIndex({ users, table }: { users: Paginated<UserRow>; table: TableState }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('users.title'), href: route('users.index') }];

    const columns: Column<UserRow>[] = [
        { key: 'name', label: t('users.fields.name'), render: (row) => <span className="font-medium">{row.name}</span> },
        { key: 'email', label: t('users.fields.email') },
        {
            key: 'role',
            label: t('users.fields.role'),
            sortable: false,
            render: (row) => (row.role ? t(`roles.${row.role}`) : '—'),
        },
        { key: 'locale', label: t('users.fields.locale'), sortable: false, render: (row) => row.locale ?? '—' },
        {
            key: 'is_active',
            label: t('users.fields.is_active'),
            sortable: false,
            render: (row) => (
                <Badge variant={row.is_active ? 'default' : 'secondary'}>
                    {row.is_active ? t('common.status.active') : t('common.status.inactive')}
                </Badge>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('users.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('users.title')} description={t('users.subtitle')}>
                    {can('users.create') && (
                        <Button asChild>
                            <Link href={route('users.create')}>
                                <Plus className="h-4 w-4" />
                                {t('users.create')}
                            </Link>
                        </Button>
                    )}
                </PageHeader>

                <DataTable
                    columns={columns}
                    rows={users}
                    table={table}
                    routeName="users.index"
                    rowKey={(row) => row.id}
                    actions={(row) => (
                        <div className="flex justify-end gap-1">
                            {can('users.update') && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('users.edit', row.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            {can('users.delete') && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    title={row.is_active ? t('common.actions.deactivate') : t('common.actions.activate')}
                                    onClick={() =>
                                        router.put(
                                            route('users.status', row.id),
                                            { is_active: !row.is_active },
                                            { preserveScroll: true },
                                        )
                                    }
                                >
                                    <Power className="h-4 w-4" />
                                </Button>
                            )}
                        </div>
                    )}
                />
            </div>
        </AppLayout>
    );
}
