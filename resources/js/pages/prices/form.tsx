import { Field } from '@/components/field';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

interface ProductOption {
    id: number;
    label: string;
}

interface CustomerOption {
    id: number;
    name: string;
}

interface Price {
    id: number;
    product_id: number;
    customer_id: number | null;
    season: string | null;
    price_per_stem: string;
    valid_from: string;
    valid_to: string | null;
    is_active: boolean;
    notes: string | null;
}

interface PriceFormData {
    [key: string]: string | boolean;
    product_id: string;
    customer_id: string;
    season: string;
    price_per_stem: string;
    valid_from: string;
    valid_to: string;
    is_active: boolean;
    notes: string;
}

const ALL_CUSTOMERS = 'all';
const ALL_YEAR = 'all_year';

export default function PriceForm({
    price,
    products,
    customers,
    seasons,
}: {
    price: Price | null;
    products: ProductOption[];
    customers: CustomerOption[];
    seasons: string[];
}) {
    const { t } = useTranslations();
    const editing = price !== null;

    const form = useForm<PriceFormData>({
        product_id: price ? String(price.product_id) : '',
        customer_id: price?.customer_id ? String(price.customer_id) : ALL_CUSTOMERS,
        season: price?.season ?? ALL_YEAR,
        price_per_stem: price?.price_per_stem ?? '0.0000',
        valid_from: price?.valid_from?.slice(0, 10) ?? new Date().toISOString().slice(0, 10),
        valid_to: price?.valid_to?.slice(0, 10) ?? '',
        is_active: price?.is_active ?? true,
        notes: price?.notes ?? '',
    });

    const { data, setData, processing, errors } = form;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('prices.title'), href: route('prices.index') },
        {
            title: editing ? t('prices.edit') : t('prices.create'),
            href: editing ? route('prices.edit', price.id) : route('prices.create'),
        },
    ];

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        // "Todos los clientes" y "todo el año" se guardan como null.
        form.transform((payload) => ({
            ...payload,
            customer_id: payload.customer_id === ALL_CUSTOMERS ? '' : payload.customer_id,
            season: payload.season === ALL_YEAR ? '' : payload.season,
        }));

        if (editing) {
            form.put(route('prices.update', price.id));

            return;
        }

        form.post(route('prices.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={editing ? t('prices.edit') : t('prices.create')} />

            <form onSubmit={submit} className="space-y-4 p-4">
                <PageHeader title={editing ? t('prices.edit') : t('prices.create')} description={t('prices.subtitle')}>
                    <Button asChild variant="outline" type="button">
                        <Link href={route('prices.index')}>{t('common.actions.cancel')}</Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('common.actions.save')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                        <Field label={t('prices.fields.product')} error={errors.product_id} required>
                            <Select value={data.product_id} onValueChange={(value) => setData('product_id', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder={t('prices.fields.product')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {products.map((product) => (
                                        <SelectItem key={product.id} value={String(product.id)}>
                                            {product.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('prices.fields.customer')} error={errors.customer_id}>
                            <Select value={data.customer_id} onValueChange={(value) => setData('customer_id', value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL_CUSTOMERS}>{t('prices.base_price')}</SelectItem>
                                    {customers.map((customer) => (
                                        <SelectItem key={customer.id} value={String(customer.id)}>
                                            {customer.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('prices.fields.season')} error={errors.season}>
                            <Select value={data.season} onValueChange={(value) => setData('season', value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={ALL_YEAR}>{t('prices.all_year')}</SelectItem>
                                    {seasons.map((season) => (
                                        <SelectItem key={season} value={season}>
                                            {t(`prices.seasons.${season}`)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('prices.fields.price_per_stem')} error={errors.price_per_stem} required>
                            <Input
                                type="number"
                                step="0.0001"
                                min="0.0001"
                                value={data.price_per_stem}
                                onChange={(event) => setData('price_per_stem', event.target.value)}
                            />
                        </Field>

                        <Field label={t('prices.fields.valid_from')} error={errors.valid_from} required>
                            <Input type="date" value={data.valid_from} onChange={(event) => setData('valid_from', event.target.value)} />
                        </Field>

                        <Field label={t('prices.fields.valid_to')} error={errors.valid_to} hint={t('prices.no_end_date')}>
                            <Input type="date" value={data.valid_to} onChange={(event) => setData('valid_to', event.target.value)} />
                        </Field>

                        <Field label={t('prices.fields.notes')} error={errors.notes} className="md:col-span-2">
                            <Textarea value={data.notes} onChange={(event) => setData('notes', event.target.value)} />
                        </Field>

                        <label className="flex items-center gap-2 text-sm md:col-span-2">
                            <Checkbox checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked === true)} />
                            {t('prices.fields.is_active')}
                        </label>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
