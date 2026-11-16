import { Field } from '@/components/field';
import { FlashMessages } from '@/components/flash-messages';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { Upload } from 'lucide-react';
import { type FormEventHandler, useState } from 'react';

interface ImportFieldInfo {
    key: string;
    label: string;
    required: boolean;
    example: string | null;
}

interface ImportType {
    type: string;
    label: string;
    fields: ImportFieldInfo[];
}

interface ImportRun {
    id: number;
    type: string;
    original_filename: string;
    status: string;
    total_rows: number;
    valid_rows: number;
    invalid_rows: number;
    committed_rows: number;
    created_at: string;
    user: { id: number; name: string } | null;
}

interface UploadFormData {
    [key: string]: string | number | File | null;
    type: string;
    file: File | null;
    header_row: number;
}

export default function ImportsIndex({ types, runs }: { types: ImportType[]; runs: ImportRun[] }) {
    const { t } = useTranslations();
    const [selectedType, setSelectedType] = useState(types[0]?.type ?? 'customers');

    const { data, setData, post, processing, errors } = useForm<UploadFormData>({
        type: types[0]?.type ?? 'customers',
        file: null,
        header_row: 1,
    });

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('imports.title'), href: route('imports.index') }];

    const currentType = types.find((type) => type.type === selectedType);

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        post(route('imports.store'), { forceFormData: true });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('imports.title')} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={t('imports.title')} description={t('imports.subtitle')} />

                <div className="grid gap-4 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{t('imports.upload')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-4">
                                <Field label={t('imports.fields.type')} error={errors.type} required>
                                    <Select
                                        value={data.type}
                                        onValueChange={(value) => {
                                            setData('type', value);
                                            setSelectedType(value);
                                        }}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {types.map((type) => (
                                                <SelectItem key={type.type} value={type.type}>
                                                    {type.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </Field>

                                <Field label={t('imports.fields.file')} error={errors.file} required>
                                    <Input
                                        type="file"
                                        accept=".xlsx,.xls,.csv,.txt"
                                        onChange={(event) => setData('file', event.target.files?.[0] ?? null)}
                                    />
                                </Field>

                                <Field label={t('imports.fields.header_row')} error={errors.header_row} required>
                                    <Input
                                        type="number"
                                        min={1}
                                        max={20}
                                        value={data.header_row}
                                        onChange={(event) => setData('header_row', Number(event.target.value))}
                                    />
                                </Field>

                                <Button type="submit" disabled={processing || data.file === null}>
                                    <Upload className="h-4 w-4" />
                                    {t('imports.upload')}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                {t('imports.expected_columns')} · {currentType?.label}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('imports.fields.field')}</TableHead>
                                        <TableHead>{t('imports.fields.example')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {currentType?.fields.map((field) => (
                                        <TableRow key={field.key}>
                                            <TableCell>
                                                {field.label}
                                                {field.required && (
                                                    <span className="ml-1 text-xs text-destructive">({t('imports.required')})</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">{field.example ?? '—'}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">{t('imports.recent_runs')}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        {runs.length === 0 ? (
                            <p className="px-6 pb-6 text-sm text-muted-foreground">{t('imports.no_runs')}</p>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('imports.fields.file')}</TableHead>
                                        <TableHead>{t('imports.fields.type')}</TableHead>
                                        <TableHead>{t('activity.fields.user')}</TableHead>
                                        <TableHead>{t('imports.fields.row')}</TableHead>
                                        <TableHead>{t('common.status.active')}</TableHead>
                                        <TableHead className="w-px">&nbsp;</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {runs.map((run) => (
                                        <TableRow key={run.id}>
                                            <TableCell className="font-medium">{run.original_filename}</TableCell>
                                            <TableCell>{t(`imports.types.${run.type}`)}</TableCell>
                                            <TableCell>{run.user?.name ?? '—'}</TableCell>
                                            <TableCell>
                                                {t('imports.summary', {
                                                    total: run.total_rows,
                                                    valid: run.valid_rows,
                                                    invalid: run.invalid_rows,
                                                })}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={run.status === 'committed' ? 'default' : 'secondary'}>
                                                    {t(`imports.status.${run.status}`)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Button asChild variant="ghost" size="sm">
                                                    <Link href={route('imports.show', run.id)}>{t('common.actions.view')}</Link>
                                                </Button>
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
