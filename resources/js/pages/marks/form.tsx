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

interface Option {
    id: number;
    name: string;
}

interface Mark {
    id: number;
    customer_id: number;
    cargo_agency_id: number | null;
    code: string;
    consignee: string | null;
    destination_country: string | null;
    destination_city: string | null;
    is_active: boolean;
    notes: string | null;
}

interface MarkFormData {
    [key: string]: string | boolean;
    customer_id: string;
    cargo_agency_id: string;
    code: string;
    consignee: string;
    destination_country: string;
    destination_city: string;
    is_active: boolean;
    notes: string;
}

const NO_AGENCY = 'none';

export default function MarkForm({
    mark,
    customers,
    cargoAgencies,
}: {
    mark: Mark | null;
    customers: Option[];
    cargoAgencies: Option[];
}) {
    const { t } = useTranslations();
    const editing = mark !== null;

    const form = useForm<MarkFormData>({
        customer_id: mark ? String(mark.customer_id) : '',
        cargo_agency_id: mark?.cargo_agency_id ? String(mark.cargo_agency_id) : NO_AGENCY,
        code: mark?.code ?? '',
        consignee: mark?.consignee ?? '',
        destination_country: mark?.destination_country ?? '',
        destination_city: mark?.destination_city ?? '',
        is_active: mark?.is_active ?? true,
        notes: mark?.notes ?? '',
    });

    const { data, setData, processing, errors } = form;

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('marks.title'), href: route('marks.index') },
        {
            title: editing ? t('marks.edit') : t('marks.create'),
            href: editing ? route('marks.edit', mark.id) : route('marks.create'),
        },
    ];

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        // El selector usa "none" para la opción vacía; el servidor espera null.
        form.transform((payload) => ({
            ...payload,
            cargo_agency_id: payload.cargo_agency_id === NO_AGENCY ? '' : payload.cargo_agency_id,
        }));

        if (editing) {
            form.put(route('marks.update', mark.id));

            return;
        }

        form.post(route('marks.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={editing ? t('marks.edit') : t('marks.create')} />

            <form onSubmit={submit} className="space-y-4 p-4">
                <PageHeader title={editing ? t('marks.edit') : t('marks.create')} description={t('common.required_fields')}>
                    <Button asChild variant="outline" type="button">
                        <Link href={route('marks.index')}>{t('common.actions.cancel')}</Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('common.actions.save')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                        <Field label={t('marks.fields.customer')} error={errors.customer_id} required>
                            <Select value={data.customer_id} onValueChange={(value) => setData('customer_id', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder={t('marks.fields.customer')} />
                                </SelectTrigger>
                                <SelectContent>
                                    {customers.map((customer) => (
                                        <SelectItem key={customer.id} value={String(customer.id)}>
                                            {customer.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('marks.fields.code')} error={errors.code} required>
                            <Input value={data.code} onChange={(event) => setData('code', event.target.value)} />
                        </Field>

                        <Field label={t('marks.fields.consignee')} error={errors.consignee}>
                            <Input value={data.consignee} onChange={(event) => setData('consignee', event.target.value)} />
                        </Field>

                        <Field label={t('marks.fields.cargo_agency')} error={errors.cargo_agency_id}>
                            <Select value={data.cargo_agency_id} onValueChange={(value) => setData('cargo_agency_id', value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value={NO_AGENCY}>—</SelectItem>
                                    {cargoAgencies.map((agency) => (
                                        <SelectItem key={agency.id} value={String(agency.id)}>
                                            {agency.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('marks.fields.destination_country')} error={errors.destination_country}>
                            <Input
                                value={data.destination_country}
                                onChange={(event) => setData('destination_country', event.target.value)}
                            />
                        </Field>

                        <Field label={t('marks.fields.destination_city')} error={errors.destination_city}>
                            <Input value={data.destination_city} onChange={(event) => setData('destination_city', event.target.value)} />
                        </Field>

                        <Field label={t('marks.fields.notes')} error={errors.notes} className="md:col-span-2">
                            <Textarea value={data.notes} onChange={(event) => setData('notes', event.target.value)} />
                        </Field>

                        <label className="flex items-center gap-2 text-sm md:col-span-2">
                            <Checkbox checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked === true)} />
                            {t('marks.fields.is_active')}
                        </label>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
