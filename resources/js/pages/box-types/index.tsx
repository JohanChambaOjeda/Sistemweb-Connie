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

interface BoxTypeRow extends Record<string, unknown> {
    id: number;
    code: string;
    name: string;
    full_box_factor: string;
    default_bunches_per_box: number;
    is_active: boolean;
}

export default function BoxTypesIndex({ boxTypes, table }: { boxTypes: Paginated<BoxTypeRow>; table: TableState }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('box_types.title'), href: route('box-types.index') }];

    const columns: Column<BoxTypeRow>[] = [
        { key: 'code', label: t('box_types.fields.code'), render: (row) => <span className="font-medium">{row.code}</span> },
        { key: 'name', label: t('box_types.fields.name') },
        { key: 'full_box_factor', label: t('box_types.fields.full_box_factor'), className: 'text-right' },
        { key: 'default_bunches_per_box', label: t('box_types.fields.default_bunches_per_box'), className: 'text-right' },
        {
            key: 'is_active',
            label: t('box_types.fields.is_active'),
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
            <Head title={t('box_types.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('box_types.title')}>
                    {can('box_types.create') && (
                        <Button asChild>
                            <Link href={route('box-types.create')}>
                                <Plus className="h-4 w-4" />
                                {t('box_types.create')}
                            </Link>
                        </Button>
                    )}
                </PageHeader>

                <DataTable
                    columns={columns}
                    rows={boxTypes}
                    table={table}
                    routeName="box-types.index"
                    rowKey={(row) => row.id}
                    actions={(row) => (
                        <div className="flex justify-end gap-1">
                            {can('box_types.update') && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('box-types.edit', row.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            {can('box_types.delete') && <DeleteRecordButton url={route('box-types.destroy', row.id)} name={row.code} />}
                        </div>
                    )}
                />
            </div>
        </AppLayout>
    );
}
