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

interface MarkRow extends Record<string, unknown> {
    id: number;
    code: string;
    consignee: string | null;
    destination_country: string | null;
    destination_city: string | null;
    is_active: boolean;
    customer: { id: number; name: string } | null;
    cargo_agency: { id: number; name: string } | null;
}

export default function MarksIndex({ marks, table }: { marks: Paginated<MarkRow>; table: TableState }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('marks.title'), href: route('marks.index') }];

    const columns: Column<MarkRow>[] = [
        { key: 'code', label: t('marks.fields.code'), render: (row) => <span className="font-medium">{row.code}</span> },
        {
            key: 'customer',
            label: t('marks.fields.customer'),
            sortable: false,
            render: (row) =>
                row.customer ? (
                    <Link href={route('customers.show', row.customer.id)} className="hover:underline">
                        {row.customer.name}
                    </Link>
                ) : (
                    '—'
                ),
        },
        { key: 'consignee', label: t('marks.fields.consignee'), render: (row) => row.consignee ?? '—' },
        {
            key: 'destination_country',
            label: t('marks.fields.destination_country'),
            render: (row) => [row.destination_city, row.destination_country].filter(Boolean).join(', ') || '—',
        },
        {
            key: 'cargo_agency',
            label: t('marks.fields.cargo_agency'),
            sortable: false,
            render: (row) => row.cargo_agency?.name ?? '—',
        },
        {
            key: 'is_active',
            label: t('marks.fields.is_active'),
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
            <Head title={t('marks.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('marks.title')}>
                    {can('marks.create') && (
                        <Button asChild>
                            <Link href={route('marks.create')}>
                                <Plus className="h-4 w-4" />
                                {t('marks.create')}
                            </Link>
                        </Button>
                    )}
                </PageHeader>

                <DataTable
                    columns={columns}
                    rows={marks}
                    table={table}
                    routeName="marks.index"
                    rowKey={(row) => row.id}
                    actions={(row) => (
                        <div className="flex justify-end gap-1">
                            {can('marks.update') && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('marks.edit', row.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            {can('marks.delete') && <DeleteRecordButton url={route('marks.destroy', row.id)} name={row.code} />}
                        </div>
                    )}
                />
            </div>
        </AppLayout>
    );
}
