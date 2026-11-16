import { FlashMessages } from '@/components/flash-messages';
import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { Download, Play } from 'lucide-react';
import { type FormEventHandler } from 'react';

interface ImportFieldInfo {
    key: string;
    label: string;
    required: boolean;
    example: string | null;
}

interface PreviewRow {
    number: number;
    values: Record<string, string | null>;
    errors: string[];
}

interface Preview {
    total: number;
    valid: number;
    invalid: number;
    rows: PreviewRow[];
    invalid_rows: PreviewRow[];
}

interface ImportRun {
    id: number;
    type: string;
    original_filename: string;
    header_row: number;
    column_map: Record<string, number | null> | null;
    status: string;
    total_rows: number;
    valid_rows: number;
    invalid_rows: number;
    committed_rows: number;
    error_report_path: string | null;
}

interface MappingFormData {
    [key: string]: number | Record<string, number | null>;
    header_row: number;
    column_map: Record<string, number | null>;
}

const IGNORE = 'ignore';

export default function ImportShow({
    run,
    headers,
    fields,
    preview,
    typeLabel,
}: {
    run: ImportRun;
    headers: (string | null)[];
    fields: ImportFieldInfo[];
    preview: Preview;
    typeLabel: string;
}) {
    const { t } = useTranslations();

    const { data, setData, put, processing } = useForm<MappingFormData>({
        header_row: run.header_row,
        column_map: fields.reduce<Record<string, number | null>>((carry, field) => {
            carry[field.key] = run.column_map?.[field.key] ?? null;

            return carry;
        }, {}),
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: t('imports.title'), href: route('imports.index') },
        { title: run.original_filename, href: route('imports.show', run.id) },
    ];

    const saveMapping: FormEventHandler = (event) => {
        event.preventDefault();

        put(route('imports.update', run.id), { preserveScroll: true });
    };

    const setColumn = (key: string, value: string) => {
        setData('column_map', {
            ...data.column_map,
            [key]: value === IGNORE ? null : Number(value),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={run.original_filename} />

            <div className="space-y-4 p-4">
                <FlashMessages />

                <PageHeader title={run.original_filename} description={`${typeLabel} · ${t(`imports.status.${run.status}`)}`}>
                    <Button asChild variant="outline">
                        <Link href={route('imports.index')}>{t('common.actions.back')}</Link>
                    </Button>
                    {run.error_report_path && (
                        <Button asChild variant="outline">
                            <a href={route('imports.report', run.id)}>
                                <Download className="h-4 w-4" />
                                {t('imports.download_report')}
                            </a>
                        </Button>
                    )}
                    <Button
                        disabled={preview.valid === 0}
                        onClick={() => router.post(route('imports.commit', run.id))}
                    >
                        <Play className="h-4 w-4" />
                        {t('imports.commit')}
                    </Button>
                </PageHeader>

                <Card>
                    <CardContent className="flex flex-wrap items-center gap-4 p-4 text-sm">
                        <span>
                            {t('imports.summary', {
                                total: preview.total,
                                valid: preview.valid,
                                invalid: preview.invalid,
                            })}
                        </span>
                        {run.committed_rows > 0 && (
                            <Badge variant="default">
                                {t('imports.messages.committed', {
                                    imported: run.committed_rows,
                                    rejected: run.invalid_rows,
                                })}
                            </Badge>
                        )}
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">{t('imports.mapping')}</CardTitle>
                        <p className="text-sm text-muted-foreground">{t('imports.mapping_hint')}</p>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={saveMapping} className="space-y-4">
                            <div className="max-w-xs">
                                <Label htmlFor="header_row" className="mb-1.5 block">
                                    {t('imports.fields.header_row')}
                                </Label>
                                <Input
                                    id="header_row"
                                    type="number"
                                    min={1}
                                    max={20}
                                    value={data.header_row}
                                    onChange={(event) => setData('header_row', Number(event.target.value))}
                                />
                            </div>

                            <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                                {fields.map((field) => (
                                    <div key={field.key}>
                                        <Label className="mb-1.5 block">
                                            {field.label}
                                            {field.required && <span className="ml-0.5 text-destructive">*</span>}
                                        </Label>
                                        <Select
                                            value={data.column_map[field.key] === null ? IGNORE : String(data.column_map[field.key])}
                                            onValueChange={(value) => setColumn(field.key, value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value={IGNORE}>{t('imports.ignore_column')}</SelectItem>
                                                {headers.map((header, index) => (
                                                    <SelectItem key={index} value={String(index)}>
                                                        {header ?? `#${index + 1}`}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                ))}
                            </div>

                            <Button type="submit" disabled={processing}>
                                {t('common.actions.save')}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">{t('imports.preview')}</CardTitle>
                        <p className="text-sm text-muted-foreground">{t('imports.preview_hint')}</p>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-16">{t('imports.fields.row')}</TableHead>
                                    {fields.map((field) => (
                                        <TableHead key={field.key}>{field.label}</TableHead>
                                    ))}
                                    <TableHead>{t('imports.fields.errors')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {preview.rows.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={fields.length + 2} className="py-10 text-center text-muted-foreground">
                                            {t('common.table.no_results')}
                                        </TableCell>
                                    </TableRow>
                                )}

                                {preview.rows.map((row) => (
                                    <TableRow key={row.number} className={row.errors.length > 0 ? 'bg-destructive/5' : undefined}>
                                        <TableCell className="text-muted-foreground">{row.number}</TableCell>
                                        {fields.map((field) => (
                                            <TableCell key={field.key}>{row.values[field.key] ?? '—'}</TableCell>
                                        ))}
                                        <TableCell className="text-xs text-destructive">
                                            {row.errors.length > 0 ? row.errors.join(' · ') : ''}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {preview.invalid_rows.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{t('imports.rejected_rows')}</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-16">{t('imports.fields.row')}</TableHead>
                                        <TableHead>{t('imports.fields.errors')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {preview.invalid_rows.map((row) => (
                                        <TableRow key={row.number}>
                                            <TableCell className="text-muted-foreground">{row.number}</TableCell>
                                            <TableCell className="text-sm">{row.errors.join(' · ')}</TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
