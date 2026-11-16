import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useTranslations } from '@/lib/i18n';
import { cn } from '@/lib/utils';
import type { Paginated, TableState } from '@/types';
import { router } from '@inertiajs/react';
import { ArrowDown, ArrowUp, ChevronsUpDown, Search } from 'lucide-react';
import { type ReactNode, useEffect, useRef, useState } from 'react';

export interface Column<T> {
    /** Nombre de la columna en el servidor; también se usa para ordenar. */
    key: string;
    label: string;
    sortable?: boolean;
    className?: string;
    render?: (row: T) => ReactNode;
}

interface DataTableProps<T> {
    columns: Column<T>[];
    rows: Paginated<T>;
    table: TableState;
    /** Ruta del listado (nombre de ziggy), a la que se mandan búsqueda y orden. */
    routeName: string;
    routeParams?: Record<string, string | number>;
    rowKey?: (row: T) => string | number;
    actions?: (row: T) => ReactNode;
    emptyMessage?: string;
}

const PER_PAGE_OPTIONS = [10, 25, 50, 100];

/**
 * Tabla reutilizable: la búsqueda, el orden y la paginación se resuelven en el
 * servidor y viajan por la URL, así el enlace se puede compartir.
 */
export function DataTable<T extends Record<string, unknown>>({
    columns,
    rows,
    table,
    routeName,
    routeParams = {},
    rowKey,
    actions,
    emptyMessage,
}: DataTableProps<T>) {
    const { t } = useTranslations();
    const [search, setSearch] = useState(table.search);
    const firstRender = useRef(true);

    useEffect(() => {
        if (firstRender.current) {
            firstRender.current = false;

            return;
        }

        const timeout = setTimeout(() => {
            visit({ search, page: 1 });
        }, 350);

        return () => clearTimeout(timeout);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [search]);

    const visit = (overrides: Record<string, string | number>) => {
        router.get(
            route(routeName, routeParams),
            {
                search: table.search,
                sort: table.sort,
                direction: table.direction,
                per_page: table.per_page,
                ...overrides,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    const toggleSort = (column: Column<T>) => {
        if (column.sortable === false) {
            return;
        }

        const direction = table.sort === column.key && table.direction === 'asc' ? 'desc' : 'asc';

        visit({ sort: column.key, direction, page: 1 });
    };

    const sortIcon = (column: Column<T>) => {
        if (table.sort !== column.key) {
            return <ChevronsUpDown className="ml-1 inline h-3 w-3 opacity-40" />;
        }

        return table.direction === 'asc' ? (
            <ArrowUp className="ml-1 inline h-3 w-3" />
        ) : (
            <ArrowDown className="ml-1 inline h-3 w-3" />
        );
    };

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="relative w-full max-w-sm">
                    <Search className="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder={t('common.table.search_placeholder')}
                        className="pl-9"
                        aria-label={t('common.actions.search')}
                    />
                </div>

                <div className="flex items-center gap-2 text-sm text-muted-foreground">
                    <span>{t('common.table.per_page')}</span>
                    <Select value={String(table.per_page)} onValueChange={(value) => visit({ per_page: value, page: 1 })}>
                        <SelectTrigger className="h-9 w-20">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {PER_PAGE_OPTIONS.map((option) => (
                                <SelectItem key={option} value={String(option)}>
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div className="rounded-lg border bg-card">
                <Table>
                    <TableHeader>
                        <TableRow>
                            {columns.map((column) => (
                                <TableHead key={column.key} className={column.className}>
                                    {column.sortable === false ? (
                                        column.label
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={() => toggleSort(column)}
                                            className="inline-flex items-center uppercase hover:text-foreground"
                                        >
                                            {column.label}
                                            {sortIcon(column)}
                                        </button>
                                    )}
                                </TableHead>
                            ))}
                            {actions && <TableHead className="w-px text-right">&nbsp;</TableHead>}
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {rows.data.length === 0 && (
                            <TableRow>
                                <TableCell colSpan={columns.length + (actions ? 1 : 0)} className="py-10 text-center text-muted-foreground">
                                    {emptyMessage ?? t('common.table.no_results')}
                                </TableCell>
                            </TableRow>
                        )}

                        {rows.data.map((row, index) => (
                            <TableRow key={rowKey ? rowKey(row) : index}>
                                {columns.map((column) => (
                                    <TableCell key={column.key} className={column.className}>
                                        {column.render ? column.render(row) : String(row[column.key] ?? '')}
                                    </TableCell>
                                ))}
                                {actions && <TableCell className="text-right whitespace-nowrap">{actions(row)}</TableCell>}
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
            </div>

            <div className="flex flex-wrap items-center justify-between gap-3 text-sm text-muted-foreground">
                <span>
                    {t('common.table.showing', {
                        from: rows.from ?? 0,
                        to: rows.to ?? 0,
                        total: rows.total,
                    })}
                </span>

                <div className="flex flex-wrap gap-1">
                    {rows.links.map((link, index) => (
                        <Button
                            key={`${link.label}-${index}`}
                            variant={link.active ? 'default' : 'outline'}
                            size="sm"
                            disabled={link.url === null}
                            onClick={() => link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })}
                            className={cn('min-w-9', link.url === null && 'opacity-50')}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
            </div>
        </div>
    );
}
