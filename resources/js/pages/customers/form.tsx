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

interface Customer {
    id: number;
    name: string;
    country: string | null;
    city: string | null;
    locale: string;
    invoice_email: string | null;
    phone: string | null;
    whatsapp: string | null;
    incoterm: string;
    credit_days: number;
    credit_limit_cents: number;
    is_active: boolean;
    notes: string | null;
}

interface CustomerFormData {
    [key: string]: string | number | boolean;
    name: string;
    country: string;
    city: string;
    locale: string;
    invoice_email: string;
    phone: string;
    whatsapp: string;
    incoterm: string;
    credit_days: number;
    credit_limit: string;
    is_active: boolean;
    notes: string;
}

const LANGUAGE_NAMES: Record<string, string> = { es: 'Español', en: 'English' };

export default function CustomerForm({
    customer,
    incoterms,
    locales,
}: {
    customer: Customer | null;
    incoterms: string[];
    locales: string[];
}) {
    const { t } = useTranslations();
    const editing = customer !== null;

    const { data, setData, post, put, processing, errors } = useForm<CustomerFormData>({
        name: customer?.name ?? '',
        country: customer?.country ?? '',
        city: customer?.city ?? '',
        locale: customer?.locale ?? 'en',
        invoice_email: customer?.invoice_email ?? '',
        phone: customer?.phone ?? '',
        whatsapp: customer?.whatsapp ?? '',
        incoterm: customer?.incoterm ?? 'FOB',
        credit_days: customer?.credit_days ?? 0,
        credit_limit: customer ? (customer.credit_limit_cents / 100).toFixed(2) : '0.00',
        is_active: customer?.is_active ?? true,
        notes: customer?.notes ?? '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('customers.title'), href: route('customers.index') },
        {
            title: editing ? t('customers.edit') : t('customers.create'),
            href: editing ? route('customers.edit', customer.id) : route('customers.create'),
        },
    ];

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        if (editing) {
            put(route('customers.update', customer.id));

            return;
        }

        post(route('customers.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={editing ? t('customers.edit') : t('customers.create')} />

            <form onSubmit={submit} className="space-y-4 p-4">
                <PageHeader
                    title={editing ? t('customers.edit') : t('customers.create')}
                    description={t('common.required_fields')}
                >
                    <Button asChild variant="outline" type="button">
                        <Link href={route('customers.index')}>{t('common.actions.cancel')}</Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('common.actions.save')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                        <Field label={t('customers.fields.name')} htmlFor="name" error={errors.name} required className="md:col-span-2">
                            <Input id="name" value={data.name} onChange={(event) => setData('name', event.target.value)} autoFocus />
                        </Field>

                        <Field label={t('customers.fields.country')} htmlFor="country" error={errors.country}>
                            <Input id="country" value={data.country} onChange={(event) => setData('country', event.target.value)} />
                        </Field>

                        <Field label={t('customers.fields.city')} htmlFor="city" error={errors.city}>
                            <Input id="city" value={data.city} onChange={(event) => setData('city', event.target.value)} />
                        </Field>

                        <Field label={t('customers.fields.invoice_email')} htmlFor="invoice_email" error={errors.invoice_email}>
                            <Input
                                id="invoice_email"
                                type="email"
                                value={data.invoice_email}
                                onChange={(event) => setData('invoice_email', event.target.value)}
                            />
                        </Field>

                        <Field label={t('customers.fields.locale')} error={errors.locale} required>
                            <Select value={data.locale} onValueChange={(value) => setData('locale', value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {locales.map((option) => (
                                        <SelectItem key={option} value={option}>
                                            {LANGUAGE_NAMES[option] ?? option}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('customers.fields.phone')} htmlFor="phone" error={errors.phone}>
                            <Input id="phone" value={data.phone} onChange={(event) => setData('phone', event.target.value)} />
                        </Field>

                        <Field label={t('customers.fields.whatsapp')} htmlFor="whatsapp" error={errors.whatsapp}>
                            <Input id="whatsapp" value={data.whatsapp} onChange={(event) => setData('whatsapp', event.target.value)} />
                        </Field>

                        <Field label={t('customers.fields.incoterm')} error={errors.incoterm} required>
                            <Select value={data.incoterm} onValueChange={(value) => setData('incoterm', value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {incoterms.map((option) => (
                                        <SelectItem key={option} value={option}>
                                            {option}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('customers.fields.credit_days')} htmlFor="credit_days" error={errors.credit_days} required>
                            <Input
                                id="credit_days"
                                type="number"
                                min={0}
                                max={365}
                                value={data.credit_days}
                                onChange={(event) => setData('credit_days', Number(event.target.value))}
                            />
                        </Field>

                        <Field
                            label={t('customers.fields.credit_limit')}
                            htmlFor="credit_limit"
                            error={errors.credit_limit_cents ?? errors.credit_limit}
                            required
                        >
                            <Input
                                id="credit_limit"
                                type="number"
                                step="0.01"
                                min={0}
                                value={data.credit_limit}
                                onChange={(event) => setData('credit_limit', event.target.value)}
                            />
                        </Field>

                        <Field label={t('customers.fields.notes')} htmlFor="notes" error={errors.notes} className="md:col-span-2">
                            <Textarea id="notes" value={data.notes} onChange={(event) => setData('notes', event.target.value)} />
                        </Field>

                        <label className="flex items-center gap-2 text-sm md:col-span-2">
                            <Checkbox
                                checked={data.is_active}
                                onCheckedChange={(checked) => setData('is_active', checked === true)}
                            />
                            {t('customers.fields.is_active')}
                        </label>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
