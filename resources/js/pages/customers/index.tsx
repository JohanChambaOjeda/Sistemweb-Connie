import { DataTable, type Column } from '@/components/data-table';
import { DeleteRecordButton } from '@/components/delete-record-button';
import { FlashMessages } from '@/components/flash-messages';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { useAuthorization } from '@/lib/authorization';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem, Paginated, TableState } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';

interface CustomerRow extends Record<string, unknown> {
    id: number;
    name: string;
    country: string | null;
    city: string | null;
    incoterm: string;
    credit_days: number;
    credit_limit_cents: number;
    is_active: boolean;
    contacts_count: number;
    marks_count: number;
}

export default function CustomersIndex({ customers, table }: { customers: Paginated<CustomerRow>; table: TableState }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('customers.title'), href: route('customers.index') }];

    const columns: Column<CustomerRow>[] = [
        {
            key: 'name',
            label: t('customers.fields.name'),
            render: (row) => (
                <Link href={route('customers.show', row.id)} className="font-medium hover:underline">
                    {row.name}
                </Link>
            ),
        },
        { key: 'country', label: t('customers.fields.country'), render: (row) => row.country ?? '—' },
        { key: 'city', label: t('customers.fields.city'), render: (row) => row.city ?? '—' },
        { key: 'incoterm', label: t('customers.fields.incoterm'), sortable: false },
        { key: 'credit_days', label: t('customers.fields.credit_days') },
        {
            key: 'credit_limit_cents',
            label: t('customers.fields.credit_limit'),
            sortable: false,
            className: 'text-right',
            render: (row) => `$ ${(row.credit_limit_cents / 100).toLocaleString('en-US', { minimumFractionDigits: 2 })}`,
        },
        {
            key: 'contacts_count',
            label: t('customers.fields.contacts'),
            sortable: false,
            className: 'text-center',
            render: (row) => `${row.contacts_count} / ${row.marks_count}`,
        },
        {
            key: 'is_active',
            label: t('customers.fields.is_active'),
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
            <Head title={t('customers.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('customers.title')} description={t('customers.subtitle')}>
                    {can('customers.create') && (
                        <Button asChild>
                            <Link href={route('customers.create')}>
                                <Plus className="h-4 w-4" />
                                {t('customers.create')}
                            </Link>
                        </Button>
                    )}
                </PageHeader>

                <DataTable
                    columns={columns}
                    rows={customers}
                    table={table}
                    routeName="customers.index"
                    rowKey={(row) => row.id}
                    actions={(row) => (
                        <div className="flex justify-end gap-1">
                            {can('customers.update') && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('customers.edit', row.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            {can('customers.delete') && (
                                <DeleteRecordButton url={route('customers.destroy', row.id)} name={row.name} />
                            )}
                        </div>
                    )}
                />
            </div>
        </AppLayout>
    );
}
