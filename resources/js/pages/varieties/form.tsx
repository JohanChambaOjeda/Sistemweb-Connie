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
    color: string | null;
    type: string;
    photo_url: string | null;
    is_active: boolean;
}

interface VarietyFormData {
    [key: string]: string | boolean | File | null;
    name: string;
    color: string;
    type: string;
    photo: File | null;
    is_active: boolean;
}

export default function VarietyForm({ variety, types }: { variety: Variety | null; types: string[] }) {
    const { t } = useTranslations();
    const editing = variety !== null;

    const form = useForm<VarietyFormData>({
        name: variety?.name ?? '',
        color: variety?.color ?? '',
        type: variety?.type ?? 'standard',
        photo: null,
        is_active: variety?.is_active ?? true,
    });

    const { data, setData, processing, errors } = form;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('varieties.title'), href: route('varieties.index') },
        {
            title: editing ? t('varieties.edit') : t('varieties.create'),
            href: editing ? route('varieties.edit', variety.id) : route('varieties.create'),
        },
    ];

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        // La foto es un archivo: el formulario viaja como multipart y la
        // actualización se manda por POST con _method=put, como pide Inertia.
        if (editing) {
            form.transform((payload) => ({ ...payload, _method: 'put' }));
            form.post(route('varieties.update', variety.id), { forceFormData: true });

            return;
        }

        form.post(route('varieties.store'), { forceFormData: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={editing ? t('varieties.edit') : t('varieties.create')} />

            <form onSubmit={submit} className="space-y-4 p-4">
                <PageHeader title={editing ? t('varieties.edit') : t('varieties.create')} description={t('common.required_fields')}>
                    <Button asChild variant="outline" type="button">
                        <Link href={route('varieties.index')}>{t('common.actions.cancel')}</Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('common.actions.save')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                        <Field label={t('varieties.fields.name')} error={errors.name} required>
                            <Input value={data.name} onChange={(event) => setData('name', event.target.value)} autoFocus />
                        </Field>

                        <Field label={t('varieties.fields.color')} error={errors.color}>
                            <Input value={data.color} onChange={(event) => setData('color', event.target.value)} />
                        </Field>

                        <Field label={t('varieties.fields.type')} error={errors.type} required>
                            <Select value={data.type} onValueChange={(value) => setData('type', value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {types.map((option) => (
                                        <SelectItem key={option} value={option}>
                                            {t(`varieties.types.${option}`)}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('varieties.fields.photo')} error={errors.photo}>
                            <Input
                                type="file"
                                accept="image/*"
                                onChange={(event) => setData('photo', event.target.files?.[0] ?? null)}
                            />
                        </Field>

                        {variety?.photo_url && (
                            <img
                                src={variety.photo_url}
                                alt={variety.name}
                                className="h-24 w-24 rounded-md object-cover md:col-span-2"
                            />
                        )}

                        <label className="flex items-center gap-2 text-sm md:col-span-2">
                            <Checkbox checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked === true)} />
                            {t('varieties.fields.is_active')}
                        </label>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
