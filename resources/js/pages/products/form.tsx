import { Field } from '@/components/field';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

interface Variety {
    id: number;
    name: string;
}

interface Product {
    id: number;
    flower_variety_id: number;
    stem_length_cm: number;
    stems_per_bunch: number;
    is_active: boolean;
}

interface ProductFormData {
    [key: string]: string | number | boolean;
    flower_variety_id: string;
    stem_length_cm: number;
    stems_per_bunch: number;
    is_active: boolean;
}

export default function ProductForm({
    product,
    varieties,
    defaultStemsPerBunch,
    lengthRange,
}: {
    product: Product | null;
    varieties: Variety[];
    defaultStemsPerBunch: number;
    lengthRange: { min: number; max: number };
}) {
    const { t } = useTranslations();
    const editing = product !== null;

    const { data, setData, post, put, processing, errors } = useForm<ProductFormData>({
        flower_variety_id: product ? String(product.flower_variety_id) : '',
        stem_length_cm: product?.stem_length_cm ?? 60,
        stems_per_bunch: product?.stems_per_bunch ?? defaultStemsPerBunch,
        is_active: product?.is_active ?? true,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('products.title'), href: route('products.index') },
        {
            title: editing ? t('products.edit') : t('products.create'),
            href: editing ? route('products.edit', product.id) : route('products.create'),
        },
    ];

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        if (editing) {
            put(route('products.update', product.id));

            return;
        }

        post(route('products.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={editing ? t('products.edit') : t('products.create')} />

            <form onSubmit={submit} className="space-y-4 p-4">
                <PageHeader title={editing ? t('products.edit') : t('products.create')} description={t('products.subtitle')}>
                    <Button asChild variant="outline" type="button">
                        <Link href={route('products.index')}>{t('common.actions.cancel')}</Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('common.actions.save')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-3">
                        <Field label={t('products.fields.variety')} error={errors.flower_variety_id} required>
                            <Select value={data.flower_variety_id} onValueChange={(value) => setData('flower_variety_id', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder={t('products.fields.variety')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {varieties.map((variety) => (
                                        <SelectItem key={variety.id} value={String(variety.id)}>
                                            {variety.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('products.fields.stem_length_cm')} error={errors.stem_length_cm} required>
                            <Input
                                type="number"
                                min={lengthRange.min}
                                max={lengthRange.max}
                                step={5}
                                value={data.stem_length_cm}
                                onChange={(event) => setData('stem_length_cm', Number(event.target.value))}
                            />
                        </Field>

                        <Field label={t('products.fields.stems_per_bunch')} error={errors.stems_per_bunch} required>
                            <Input
                                type="number"
                                min={1}
                                max={100}
                                value={data.stems_per_bunch}
                                onChange={(event) => setData('stems_per_bunch', Number(event.target.value))}
                            />
                        </Field>

                        <label className="flex items-center gap-2 text-sm md:col-span-3">
                            <Checkbox checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked === true)} />
                            {t('products.fields.is_active')}
                        </label>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
