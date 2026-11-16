import { Field } from '@/components/field';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

interface BoxType {
    id: number;
    code: string;
    name: string;
    full_box_factor: string;
    default_bunches_per_box: number;
    is_active: boolean;
}

interface BoxTypeFormData {
    [key: string]: string | number | boolean;
    code: string;
    name: string;
    full_box_factor: string;
    default_bunches_per_box: number;
    is_active: boolean;
}

export default function BoxTypeForm({ boxType }: { boxType: BoxType | null }) {
    const { t } = useTranslations();
    const editing = boxType !== null;

    const { data, setData, post, put, processing, errors } = useForm<BoxTypeFormData>({
        code: boxType?.code ?? '',
        name: boxType?.name ?? '',
        full_box_factor: boxType?.full_box_factor ?? '1.000',
        default_bunches_per_box: boxType?.default_bunches_per_box ?? 0,
        is_active: boxType?.is_active ?? true,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('box_types.title'), href: route('box-types.index') },
        {
            title: editing ? t('box_types.edit') : t('box_types.create'),
            href: editing ? route('box-types.edit', boxType.id) : route('box-types.create'),
        },
    ];

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        if (editing) {
            put(route('box-types.update', boxType.id));

            return;
        }

        post(route('box-types.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={editing ? t('box_types.edit') : t('box_types.create')} />

            <form onSubmit={submit} className="space-y-4 p-4">
                <PageHeader title={editing ? t('box_types.edit') : t('box_types.create')} description={t('common.required_fields')}>
                    <Button asChild variant="outline" type="button">
                        <Link href={route('box-types.index')}>{t('common.actions.cancel')}</Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('common.actions.save')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                        <Field label={t('box_types.fields.code')} error={errors.code} required>
                            <Input
                                value={data.code}
                                onChange={(event) => setData('code', event.target.value.toUpperCase())}
                                maxLength={10}
                                autoFocus
                            />
                        </Field>

                        <Field label={t('box_types.fields.name')} error={errors.name} required>
                            <Input value={data.name} onChange={(event) => setData('name', event.target.value)} />
                        </Field>

                        <Field
                            label={t('box_types.fields.full_box_factor')}
                            error={errors.full_box_factor}
                            required
                            hint="QB = 0.25 · HB = 0.5 · FB = 1"
                        >
                            <Input
                                type="number"
                                step="0.001"
                                min="0.001"
                                value={data.full_box_factor}
                                onChange={(event) => setData('full_box_factor', event.target.value)}
                            />
                        </Field>

                        <Field
                            label={t('box_types.fields.default_bunches_per_box')}
                            error={errors.default_bunches_per_box}
                            required
                        >
                            <Input
                                type="number"
                                min={0}
                                value={data.default_bunches_per_box}
                                onChange={(event) => setData('default_bunches_per_box', Number(event.target.value))}
                            />
                        </Field>

                        <label className="flex items-center gap-2 text-sm md:col-span-2">
                            <Checkbox checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked === true)} />
                            {t('box_types.fields.is_active')}
                        </label>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
