import { Field } from '@/components/field';
import { PageHeader } from '@/components/page-header';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { type FormEventHandler } from 'react';

interface Agency {
    id: number;
    name: string;
    tax_id: string | null;
    contact_name: string | null;
    phone: string | null;
    email: string | null
    cutoff_time: string | null;
    is_active: boolean;
    notes: string | null;
}

interface AgencyFormData {
    [key: string]: string | boolean;
    name: string;
    tax_id: string;
    contact_name: string;
    phone: string;
    email: string;
    cutoff_time: string;
    is_active: boolean;
    notes: string;
}

export default function CargoAgencyForm({ agency }: { agency: Agency | null }) {
    const { t } = useTranslations();
    const editing = agency !== null;

    const { data, setData, post, put, processing, errors } = useForm<AgencyFormData>({
        name: agency?.name ?? '',
        tax_id: agency?.tax_id ?? '',
        contact_name: agency?.contact_name ?? '',
        phone: agency?.phone ?? '',
        email: agency?.email ?? '',
        cutoff_time: agency?.cutoff_time?.slice(0, 5) ?? '',
        is_active: agency?.is_active ?? true,
        notes: agency?.notes ?? '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('agencies.title'), href: route('cargo-agencies.index') },
        {
            title: editing ? t('agencies.edit') : t('agencies.create'),
            href: editing ? route('cargo-agencies.edit', agency.id) : route('cargo-agencies.create'),
        },
    ];

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        if (editing) {
            put(route('cargo-agencies.update', agency.id));

            return;
        }

        post(route('cargo-agencies.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={editing ? t('agencies.edit') : t('agencies.create')} />

            <form onSubmit={submit} className="space-y-4 p-4">
                <PageHeader title={editing ? t('agencies.edit') : t('agencies.create')} description={t('common.required_fields')}>
                    <Button asChild variant="outline" type="button">
                        <Link href={route('cargo-agencies.index')}>{t('common.actions.cancel')}</Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('common.actions.save')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                        <Field label={t('agencies.fields.name')} error={errors.name} required className="md:col-span-2">
                            <Input value={data.name} onChange={(event) => setData('name', event.target.value)} autoFocus />
                        </Field>

                        <Field label={t('agencies.fields.tax_id')} error={errors.tax_id}>
                            <Input value={data.tax_id} onChange={(event) => setData('tax_id', event.target.value)} />
                        </Field>

                        <Field label={t('agencies.fields.contact_name')} error={errors.contact_name}>
                            <Input value={data.contact_name} onChange={(event) => setData('contact_name', event.target.value)} />
                        </Field>

                        <Field label={t('agencies.fields.phone')} error={errors.phone}>
                            <Input value={data.phone} onChange={(event) => setData('phone', event.target.value)} />
                        </Field>

                        <Field label={t('agencies.fields.email')} error={errors.email}>
                            <Input type="email" value={data.email} onChange={(event) => setData('email', event.target.value)} />
                        </Field>

                        <Field label={t('agencies.fields.cutoff_time')} error={errors.cutoff_time}>
                            <Input type="time" value={data.cutoff_time} onChange={(event) => setData('cutoff_time', event.target.value)} />
                        </Field>

                        <Field label={t('agencies.fields.notes')} error={errors.notes} className="md:col-span-2">
                            <Textarea value={data.notes} onChange={(event) => setData('notes', event.target.value)} />
                        </Field>

                        <label className="flex items-center gap-2 text-sm md:col-span-2">
                            <Checkbox checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked === true)} />
                            {t('agencies.fields.is_active')}
                        </label>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
