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

interface UserPayload {
    id: number;
    name: string;
    email: string;
    locale: string | null;
    is_active: boolean;
    role: string | null;
    company_ids: number[];
}

interface RoleOption {
    value: string;
    label: string;
}

interface CompanyOption {
    id: number;
    name: string;
}

interface UserFormData {
    [key: string]: string | boolean | number[];
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    locale: string;
    is_active: boolean;
    role: string;
    company_ids: number[];
}

const LANGUAGE_NAMES: Record<string, string> = { es: 'Español', en: 'English' };

export default function UserForm({
    user,
    roles,
    companies,
    locales,
}: {
    user: UserPayload | null;
    roles: RoleOption[];
    companies: CompanyOption[];
    locales: string[];
}) {
    const { t } = useTranslations();
    const editing = user !== null;

    const { data, setData, post, put, processing, errors } = useForm<UserFormData>({
        name: user?.name ?? '',
        email: user?.email ?? '',
        password: '',
        password_confirmation: '',
        locale: user?.locale ?? 'es',
        is_active: user?.is_active ?? true,
        role: user?.role ?? roles[0]?.value ?? '',
        company_ids: user?.company_ids ?? companies.map((company) => company.id),
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('users.title'), href: route('users.index') },
        {
            title: editing ? t('users.edit') : t('users.create'),
            href: editing ? route('users.edit', user.id) : route('users.create'),
        },
    ];

    const toggleCompany = (companyId: number, checked: boolean) => {
        setData(
            'company_ids',
            checked ? [...data.company_ids, companyId] : data.company_ids.filter((id) => id !== companyId),
        );
    };

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        if (editing) {
            put(route('users.update', user.id));

            return;
        }

        post(route('users.store'));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={editing ? t('users.edit') : t('users.create')} />

            <form onSubmit={submit} className="space-y-4 p-4">
                <PageHeader title={editing ? t('users.edit') : t('users.create')} description={t('common.required_fields')}>
                    <Button asChild variant="outline" type="button">
                        <Link href={route('users.index')}>{t('common.actions.cancel')}</Link>
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {t('common.actions.save')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="grid gap-4 p-6 md:grid-cols-2">
                        <Field label={t('users.fields.name')} error={errors.name} required>
                            <Input value={data.name} onChange={(event) => setData('name', event.target.value)} autoFocus />
                        </Field>

                        <Field label={t('users.fields.email')} error={errors.email} required>
                            <Input type="email" value={data.email} onChange={(event) => setData('email', event.target.value)} />
                        </Field>

                        <Field
                            label={t('users.fields.password')}
                            error={errors.password}
                            required={!editing}
                            hint={editing ? t('users.password_hint') : undefined}
                        >
                            <Input
                                type="password"
                                autoComplete="new-password"
                                value={data.password}
                                onChange={(event) => setData('password', event.target.value)}
                            />
                        </Field>

                        <Field label={t('users.fields.password_confirmation')} error={errors.password_confirmation}>
                            <Input
                                type="password"
                                autoComplete="new-password"
                                value={data.password_confirmation}
                                onChange={(event) => setData('password_confirmation', event.target.value)}
                            />
                        </Field>

                        <Field label={t('users.fields.role')} error={errors.role} required>
                            <Select value={data.role} onValueChange={(value) => setData('role', value)}>
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {roles.map((role) => (
                                        <SelectItem key={role.value} value={role.value}>
                                            {role.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </Field>

                        <Field label={t('users.fields.locale')} error={errors.locale} required>
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

                        <Field label={t('users.fields.companies')} error={errors.company_ids} required className="md:col-span-2">
                            <div className="space-y-2">
                                {companies.map((company) => (
                                    <label key={company.id} className="flex items-center gap-2 text-sm">
                                        <Checkbox
                                            checked={data.company_ids.includes(company.id)}
                                            onCheckedChange={(checked) => toggleCompany(company.id, checked === true)}
                                        />
                                        {company.name}
                                    </label>
                                ))}
                            </div>
                        </Field>

                        <label className="flex items-center gap-2 text-sm md:col-span-2">
                            <Checkbox checked={data.is_active} onCheckedChange={(checked) => setData('is_active', checked === true)} />
                            {t('users.fields.is_active')}
                        </label>
                    </CardContent>
                </Card>
            </form>
        </AppLayout>
    );
}
