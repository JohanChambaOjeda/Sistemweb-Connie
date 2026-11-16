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

interface ProductRow extends Record<string, unknown> {
    id: number;
    stem_length_cm: number;
    stems_per_bunch: number;
    is_active: boolean;
    label: string;
    flower_variety: { id: number; name: string; color: string | null } | null;
}

export default function ProductsIndex({ products, table }: { products: Paginated<ProductRow>; table: TableState }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('products.title'), href: route('products.index') }];

    const columns: Column<ProductRow>[] = [
        {
            key: 'label',
            label: t('products.fields.label'),
            sortable: false,
            render: (row) => <span className="font-medium">{row.label}</span>,
        },
        {
            key: 'flower_variety',
            label: t('products.fields.variety'),
            sortable: false,
            render: (row) => row.flower_variety?.name ?? '—',
        },
        { key: 'stem_length_cm', label: t('products.fields.stem_length_cm'), className: 'text-right' },
        { key: 'stems_per_bunch', label: t('products.fields.stems_per_bunch'), className: 'text-right' },
        {
            key: 'is_active',
            label: t('products.fields.is_active'),
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
            <Head title={t('products.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('products.title')} description={t('products.subtitle')}>
                    {can('products.create') && (
                        <Button asChild>
                            <Link href={route('products.create')}>
                                <Plus className="h-4 w-4" />
                                {t('products.create')}
                            </Link>
                        </Button>
                    )}
                </PageHeader>

                <DataTable
                    columns={columns}
                    rows={products}
                    table={table}
                    routeName="products.index"
                    rowKey={(row) => row.id}
                    actions={(row) => (
                        <div className="flex justify-end gap-1">
                            {can('products.update') && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('products.edit', row.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            {can('products.delete') && <DeleteRecordButton url={route('products.destroy', row.id)} name={row.label} />}
                        </div>
                    )}
                />
            </div>
        </AppLayout>
    );
}
