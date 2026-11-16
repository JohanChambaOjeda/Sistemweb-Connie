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

interface PriceRow extends Record<string, unknown> {
    id: number;
    season: string | null;
    price_per_stem: string;
    valid_from: string;
    valid_to: string | null;
    is_active: boolean;
    customer: { id: number; name: string } | null;
    product: { id: number; label: string; flower_variety: { id: number; name: string } | null } | null;
}

export default function PricesIndex({ prices, table }: { prices: Paginated<PriceRow>; table: TableState }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('prices.title'), href: route('prices.index') }];

    const columns: Column<PriceRow>[] = [
        {
            key: 'product',
            label: t('prices.fields.product'),
            sortable: false,
            render: (row) => <span className="font-medium">{row.product?.label ?? '—'}</span>,
        },
        {
            key: 'customer',
            label: t('prices.fields.customer'),
            sortable: false,
            render: (row) =>
                row.customer ? (
                    <Link href={route('customers.show', row.customer.id)} className="hover:underline">
                        {row.customer.name}
                    </Link>
                ) : (
                    <span className="text-muted-foreground">{t('prices.base_price')}</span>
                ),
        },
        {
            key: 'season',
            label: t('prices.fields.season'),
            sortable: false,
            render: (row) =>
                row.season ? <Badge variant="outline">{t(`prices.seasons.${row.season}`)}</Badge> : t('prices.all_year'),
        },
        {
            key: 'price_per_stem',
            label: t('prices.fields.price_per_stem'),
            className: 'text-right',
            render: (row) => `$ ${Number(row.price_per_stem).toFixed(4)}`,
        },
        { key: 'valid_from', label: t('prices.fields.valid_from') },
        { key: 'valid_to', label: t('prices.fields.valid_to'), render: (row) => row.valid_to ?? t('prices.no_end_date') },
        {
            key: 'is_active',
            label: t('prices.fields.is_active'),
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
            <Head title={t('prices.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('prices.title')} description={t('prices.subtitle')}>
                    {can('product_prices.create') && (
                        <Button asChild>
                            <Link href={route('prices.create')}>
                                <Plus className="h-4 w-4" />
                                {t('prices.create')}
                            </Link>
                        </Button>
                    )}
                </PageHeader>

                <DataTable
                    columns={columns}
                    rows={prices}
                    table={table}
                    routeName="prices.index"
                    rowKey={(row) => row.id}
                    actions={(row) => (
                        <div className="flex justify-end gap-1">
                            {can('product_prices.update') && (
                                <Button asChild variant="ghost" size="sm">
                                    <Link href={route('prices.edit', row.id)}>
                                        <Pencil className="h-4 w-4" />
                                    </Link>
                                </Button>
                            )}
                            {can('product_prices.delete') && (
                                <DeleteRecordButton url={route('prices.destroy', row.id)} name={row.product?.label ?? ''} />
                            )}
                        </div>
                    )}
                />
            </div>
        </AppLayout>
    );
}
