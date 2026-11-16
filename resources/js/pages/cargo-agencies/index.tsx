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

interface AgencyRow extends Record<string, unknown> {
    id: number;
    name: string;
    tax_id: string | null;
    contact_name: string | null;
    phone: string | null;
    email: string | null;
    cutoff_time: string | null;
    is_active: boolean;
}

export default function CargoAgenciesIndex({ agencies, table }: { agencies: Paginated<AgencyRow>; table: TableState }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('agencies.title'), href: route('cargo-agencies.index') }];

    const columns: Column<AgencyRow>[] = [
        { key: 'name', label: t('agencies.fields.name'), render: (row) => <span className="font-medium">{row.name}</span> },
        { key: 'tax_id', label: t('agencies.fields.tax_id'), render: (row) => row.tax_id ?? '—' },
        { key: 'contact_name', label: t('agencies.fields.contact_name'), sortable: false, render: (row) => row.contact_name ?? '—' },
        { key: 'phone', label: t('agencies.fields.phone'), sortable: false, render: (row) => row.phone ?? '—' },
        { key: 'email', label: t('agencies.fields.email'), sortable: false, render: (row) => row.email ?? '—' },
        { key: 'cutoff_time', label: t('agencies.fields.cutoff_time'), render: (row) => row.cutoff_time?.slice(0, 5) ?? '—' },
        {
            key: 'is_active',
            label: t('agencies.fields.is_active'),
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
            <Head title={t('agencies.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('agencies.title')}>
                    {can('cargo_agencies.create') && (
                        <Button asChild>
                            <Link href={route('cargo-agencies.create')}>
                                <Plus className="h-4 w-4" />
                                {t('agencies.create')}
                            </Link>
                        </Button>
                    )}
                </PageHeader>

                <DataTable
                    columns={columns}
                    rows={agencies}
                    table={table}
                    routeName="cargo-agencies.index"
                    rowKey={(row) => row.id}
                    actions={(row) => (
                        <div className="flex justify-end gap-1">
                            {can('cargo_agencies.update') && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('cargo-agencies.edit', row.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            {can('cargo_agencies.delete') && (
                                <DeleteRecordButton url={route('cargo-agencies.destroy', row.id)} name={row.name} />
                            )}
                        </div>
                    )}
                />
            </div>
        </AppLayout>
    );
}
