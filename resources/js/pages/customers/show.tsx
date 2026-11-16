import { DeleteRecordButton } from '@/components/delete-record-button';
import { Field } from '@/components/field';
import { FlashMessages } from '@/components/flash-messages';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { useAuthorization } from '@/lib/authorization';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { type FormEventHandler, useState } from 'react';

interface Contact {
    id: number;
    name: string;
    position: string | null;
    email: string | null;
    whatsapp: string | null;
    receives_invoices: boolean;
    receives_statements: boolean;
}

interface Mark {
    id: number;
    code: string;
    consignee: string | null;
    destination_country: string | null;
    destination_city: string | null;
    is_active: boolean;
    cargo_agency: { id: number; name: string } | null;
}

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
    contacts: Contact[];
    marks: Mark[];
}

interface ContactFormData {
    [key: string]: string | number | boolean;
    customer_id: number;
    name: string;
    position: string;
    email: string;
    whatsapp: string;
    receives_invoices: boolean;
    receives_statements: boolean;
}

function ContactDialog({ customerId, contact }: { customerId: number; contact: Contact | null }) {
    const { t } = useTranslations();
    const [open, setOpen] = useState(false);
    const editing = contact !== null;

    const { data, setData, post, put, processing, errors, reset } = useForm<ContactFormData>({
        customer_id: customerId,
        name: contact?.name ?? '',
        position: contact?.position ?? '',
        email: contact?.email ?? '',
        whatsapp: contact?.whatsapp ?? '',
        receives_invoices: contact?.receives_invoices ?? false,
        receives_statements: contact?.receives_statements ?? false,
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setOpen(false);

                if (!editing) {
                    reset('name', 'position', 'email', 'whatsapp');
                }
            },
        };

        if (editing) {
            put(route('customer-contacts.update', contact.id), options);

            return;
        }

        post(route('customer-contacts.store'), options);
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                {editing ? (
                    <Button variant="ghost" size="sm">
                        <Pencil className="h-4 w-4" />
                    </Button>
                ) : (
                    <Button size="sm">
                        <Plus className="h-4 w-4" />
                        {t('contacts.create')}
                    </Button>
                )}
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={submit} className="space-y-4">
                    <DialogHeader>
                        <DialogTitle>{editing ? t('contacts.edit') : t('contacts.create')}</DialogTitle>
                    </DialogHeader>

                    <Field label={t('contacts.fields.name')} error={errors.name} required>
                        <Input value={data.name} onChange={(event) => setData('name', event.target.value)} />
                    </Field>

                    <Field label={t('contacts.fields.position')} error={errors.position}>
                        <Input value={data.position} onChange={(event) => setData('position', event.target.value)} />
                    </Field>

                    <Field label={t('contacts.fields.email')} error={errors.email}>
                        <Input type="email" value={data.email} onChange={(event) => setData('email', event.target.value)} />
                    </Field>

                    <Field label={t('contacts.fields.whatsapp')} error={errors.whatsapp}>
                        <Input value={data.whatsapp} onChange={(event) => setData('whatsapp', event.target.value)} />
                    </Field>

                    <div className="space-y-2">
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={data.receives_invoices}
                                onCheckedChange={(checked) => setData('receives_invoices', checked === true)}
                            />
                            {t('contacts.fields.receives_invoices')}
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <Checkbox
                                checked={data.receives_statements}
                                onCheckedChange={(checked) => setData('receives_statements', checked === true)}
                            />
                            {t('contacts.fields.receives_statements')}
                        </label>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                            {t('common.actions.cancel')}
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {t('common.actions.save')}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function CustomerShow({ customer }: { customer: Customer }) {
    const { t } = useTranslations();
    const { can } = useAuthorization();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('customers.title'), href: route('customers.index') },
        { title: customer.name, href: route('customers.show', customer.id) },
    ];

    const details: { label: string; value: string }[] = [
        { label: t('customers.fields.country'), value: customer.country ?? '—' },
        { label: t('customers.fields.city'), value: customer.city ?? '—' },
        { label: t('customers.fields.invoice_email'), value: customer.invoice_email ?? '—' },
        { label: t('customers.fields.phone'), value: customer.phone ?? '—' },
        { label: t('customers.fields.whatsapp'), value: customer.whatsapp ?? '—' },
        { label: t('customers.fields.incoterm'), value: customer.incoterm },
        { label: t('customers.fields.credit_days'), value: String(customer.credit_days) },
        {
            label: t('customers.fields.credit_limit'),
            value: `$ ${(customer.credit_limit_cents / 100).toLocaleString('en-US', { minimumFractionDigits: 2 })}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={customer.name} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={customer.name} description={customer.notes ?? undefined}>
                    <Badge variant={customer.is_active ? 'default' : 'secondary'}>
                        {customer.is_active ? t('common.status.active') : t('common.status.inactive')}
                    </Badge>
                    {can('customers.update') && (
                        <Button asChild variant="outline">
                            <Link href={route('customers.edit', customer.id)}>{t('common.actions.edit')}</Link>
                        </Button>
                    )}
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
                        {details.map((detail) => (
                            <div key={detail.label}>
                                <p className="text-xs tracking-wide text-muted-foreground uppercase">{detail.label}</p>
                                <p className="mt-0.5 text-sm font-medium">{detail.value}</p>
                            </div>
                        ))}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-base">{t('contacts.title')}</CardTitle>
                        {can('customer_contacts.create') && <ContactDialog customerId={customer.id} contact={null} />}
                    </CardHeader>
                    <CardContent className="p-0">
                        {customer.contacts.length === 0 ? (
                            <p className="px-6 pb-6 text-sm text-muted-foreground">{t('contacts.empty')}</p>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('contacts.fields.name')}</TableHead>
                                        <TableHead>{t('contacts.fields.position')}</TableHead>
                                        <TableHead>{t('contacts.fields.email')}</TableHead>
                                        <TableHead>{t('contacts.fields.whatsapp')}</TableHead>
                                        <TableHead>{t('contacts.fields.receives_invoices')}</TableHead>
                                        <TableHead>{t('contacts.fields.receives_statements')}</TableHead>
                                        <TableHead className="w-px">&nbsp;</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {customer.contacts.map((contact) => (
                                        <TableRow key={contact.id}>
                                            <TableCell className="font-medium">{contact.name}</TableCell>
                                            <TableCell>{contact.position ?? '—'}</TableCell>
                                            <TableCell>{contact.email ?? '—'}</TableCell>
                                            <TableCell>{contact.whatsapp ?? '—'}</TableCell>
                                            <TableCell>
                                                {contact.receives_invoices ? t('common.status.yes') : t('common.status.no')}
                                            </TableCell>
                                            <TableCell>
                                                {contact.receives_statements ? t('common.status.yes') : t('common.status.no')}
                                            </TableCell>
                                            <TableCell className="text-right whitespace-nowrap">
                                                {can('customer_contacts.update') && (
                                                    <ContactDialog customerId={customer.id} contact={contact} />
                                                )}
                                                {can('customer_contacts.delete') && (
                                                    <DeleteRecordButton
                                                        url={route('customer-contacts.destroy', contact.id)}
                                                        name={contact.name}
                                                    />
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle className="text-base">{t('marks.title')}</CardTitle>
                        {can('marks.create') && (
                            <Button asChild size="sm">
                                <Link href={route('marks.create')}>
                                    <Plus className="h-4 w-4" />
                                    {t('marks.create')}
                                </Link>
                            </Button>
                        )}
                    </CardHeader>
                    <CardContent className="p-0">
                        {customer.marks.length === 0 ? (
                            <p className="px-6 pb-6 text-sm text-muted-foreground">{t('marks.empty')}</p>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('marks.fields.code')}</TableHead>
                                        <TableHead>{t('marks.fields.consignee')}</TableHead>
                                        <TableHead>{t('marks.fields.destination_city')}</TableHead>
                                        <TableHead>{t('marks.fields.cargo_agency')}</TableHead>
                                        <TableHead>{t('marks.fields.is_active')}</TableHead>
                                        <TableHead className="w-px">&nbsp;</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {customer.marks.map((mark) => (
                                        <TableRow key={mark.id}>
                                            <TableCell className="font-medium">{mark.code}</TableCell>
                                            <TableCell>{mark.consignee ?? '—'}</TableCell>
                                            <TableCell>
                                                {[mark.destination_city, mark.destination_country].filter(Boolean).join(', ') || '—'}
                                            </TableCell>
                                            <TableCell>{mark.cargo_agency?.name ?? '—'}</TableCell>
                                            <TableCell>
                                                <Badge variant={mark.is_active ? 'default' : 'secondary'}>
                                                    {mark.is_active ? t('common.status.active') : t('common.status.inactive')}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {can('marks.update') && (
                                                    <Button asChild variant="ghost" size="sm">
                                                        <Link href={route('marks.edit', mark.id)}>
                                                            <Pencil className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                )}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
