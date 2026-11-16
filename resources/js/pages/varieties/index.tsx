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
import { Flower2, Pencil, Plus } from 'lucide-react';

interface VarietyRow extends Record<string, unknown> {
    id: number;
    name: string;
    color: string | null;
    type: string;
    photo_url: string | null;
    is_active: boolean;
    products_count: number;
}

export default function VarietiesIndex({ varieties, table }: { varieties: Paginated<VarietyRow>; table: TableState }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('varieties.title'), href: route('varieties.index') }];

    const columns: Column<VarietyRow>[] = [
        {
            key: 'photo_url',
            label: t('varieties.fields.photo'),
            sortable: false,
            className: 'w-16',
            render: (row) =>
                row.photo_url ? (
                    <img src={row.photo_url} alt={row.name} className="h-10 w-10 rounded object-cover" />
                ) : (
                    <span className="flex h-10 w-10 items-center justify-center rounded bg-muted text-muted-foreground">
                        <Flower2 className="h-4 w-4" />
                    </span>
                ),
        },
        { key: 'name', label: t('varieties.fields.name'), render: (row) => <span className="font-medium">{row.name}</span> },
        { key: 'color', label: t('varieties.fields.color'), render: (row) => row.color ?? '—' },
        { key: 'type', label: t('varieties.fields.type'), render: (row) => t(`varieties.types.${row.type}`) },
        { key: 'products_count', label: t('varieties.fields.products'), sortable: false, className: 'text-center' },
        {
            key: 'is_active',
            label: t('varieties.fields.is_active'),
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
            <Head title={t('varieties.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('varieties.title')}>
                    {can('flower_varieties.create') && (
                        <Button asChild>
                            <Link href={route('varieties.create')}>
                                <Plus className="h-4 w-4" />
                                {t('varieties.create')}
                            </Link>
                        </Button>
                    )}
                </PageHeader>

                <DataTable
                    columns={columns}
                    rows={varieties}
                    table={table}
                    routeName="varieties.index"
                    rowKey={(row) => row.id}
                    actions={(row) => (
                        <div className="flex justify-end gap-1">
                            {can('flower_varieties.update') && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('varieties.edit', row.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            {can('flower_varieties.delete') && (
                                <DeleteRecordButton url={route('varieties.destroy', row.id)} name={row.name} />
                            )}
                        </div>
                    )}
                />
            </div>
        </AppLayout>
    );
}
