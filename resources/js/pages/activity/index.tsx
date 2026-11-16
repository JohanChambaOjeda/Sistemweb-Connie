import { PageHeader } from '@/components/page-header';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { useTranslations } from '@/lib/i18n';
import type { BreadcrumbItem, Paginated } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

interface Activity {
    id: number;
    log_name: string | null;
    description: string;
    event: string | null;
    created_at: string;
    properties: { attributes?: Record<string, unknown>; old?: Record<string, unknown> } | null;
    causer: { id: number; name: string } | null;
}

interface Filters {
    user_id: number | null;
    log_name: string | null;
    from: string | null;
    to: string | null;
}

const ALL = 'all';

function Changes({ activity }: { activity: Activity }) {
    const { t } = useTranslations();
    const attributes = activity.properties?.attributes ?? {};
    const old = activity.properties?.old ?? {};
    const keys = Object.keys(attributes);

    if (keys.length === 0) {
        return <span className="text-muted-foreground">{t('activity.no_changes')}</span>;
    }

    return (
        <ul className="space-y-0.5">
            {keys.map((key) => (
                <li key={key} className="text-xs">
                    <span className="font-medium">{key}</span>: <span className="text-muted-foreground">{String(old[key] ?? '—')}</span>
                    {' → '}
                    <span>{String(attributes[key] ?? '—')}</span>
                </li>
            ))}
        </ul>
    );
}

export default function ActivityIndex({
    activities,
    filters,
    users,
    logNames,
}: {
    activities: Paginated<Activity>;
    filters: Filters;
    users: { id: number; name: string }[];
    logNames: string[];
}) {
    const { t } = useTranslations();
    const [state, setState] = useState({
        user_id: filters.user_id ? String(filters.user_id) : ALL,
        log_name: filters.log_name ?? ALL,
        from: filters.from ?? '',
        to: filters.to ?? '',
    });

    const breadcrumbs: BreadcrumbItem[] = [{ title: t('activity.title'), href: route('activity.index') }];

    const apply = (next: typeof state) => {
        setState(next);

        router.get(
            route('activity.index'),
            {
                user_id: next.user_id === ALL ? '' : next.user_id,
                log_name: next.log_name === ALL ? '' : next.log_name,
                from: next.from,
                to: next.to,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('activity.title')} />

            <div className="space-y-4 p-4">
                <PageHeader title={t('activity.title')} description={t('activity.subtitle')} />

                <Card>
                    <CardContent className="grid gap-3 p-4 md:grid-cols-4">
                        <Select value={state.user_id} onValueChange={(value) => apply({ ...state, user_id: value })}>
                            <SelectTrigger>
                                <SelectValue placeholder={t('activity.all_users')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={ALL}>{t('activity.all_users')}</SelectItem>
                                {users.map((user) => (
                                    <SelectItem key={user.id} value={String(user.id)}>
                                        {user.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select value={state.log_name} onValueChange={(value) => apply({ ...state, log_name: value })}>
                            <SelectTrigger>
                                <SelectValue placeholder={t('activity.all_models')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value={ALL}>{t('activity.all_models')}</SelectItem>
                                {logNames.map((name) => (
                                    <SelectItem key={name} value={name}>
                                        {name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Input type="date" value={state.from} onChange={(event) => apply({ ...state, from: event.target.value })} />
                        <Input type="date" value={state.to} onChange={(event) => apply({ ...state, to: event.target.value })} />
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('activity.fields.date')}</TableHead>
                                    <TableHead>{t('activity.fields.user')}</TableHead>
                                    <TableHead>{t('activity.fields.model')}</TableHead>
                                    <TableHead>{t('activity.fields.event')}</TableHead>
                                    <TableHead>{t('activity.fields.changes')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {activities.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="py-10 text-center text-muted-foreground">
                                            {t('common.table.no_results')}
                                        </TableCell>
                                    </TableRow>
                                )}

                                {activities.data.map((activity) => (
                                    <TableRow key={activity.id}>
                                        <TableCell className="whitespace-nowrap">
                                            {new Date(activity.created_at).toLocaleString()}
                                        </TableCell>
                                        <TableCell>{activity.causer?.name ?? t('activity.system')}</TableCell>
                                        <TableCell>{activity.log_name ?? '—'}</TableCell>
                                        <TableCell>
                                            <Badge variant="outline">
                                                {activity.event ? t(`activity.events.${activity.event}`) : activity.description}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Changes activity={activity} />
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <div className="flex flex-wrap items-center justify-between gap-3 text-sm text-muted-foreground">
                    <span>
                        {t('common.table.showing', {
                            from: activities.from ?? 0,
                            to: activities.to ?? 0,
                            total: activities.total,
                        })}
                    </span>
                    <div className="flex flex-wrap gap-1">
                        {activities.links.map((link, index) => (
                            <Button
                                key={`${link.label}-${index}`}
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={link.url === null}
                                onClick={() => link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })}
                                className="min-w-9"
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
