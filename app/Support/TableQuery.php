<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

/**
 * Server side search, sort and pagination for the reusable DataTable component.
 *
 * Searchable and sortable columns are whitelisted by the caller, and relation
 * columns are written with a dot: 'customer.name'.
 */
class TableQuery
{
    /** @var list<int> */
    public const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $searchable
     * @param  list<string>  $sortable
     * @return LengthAwarePaginator<int, TModel>
     */
    public static function paginate(
        Builder $query,
        Request $request,
        array $searchable = [],
        array $sortable = [],
        string $defaultSort = 'id',
        string $defaultDirection = 'asc',
    ): LengthAwarePaginator {
        self::applySearch($query, $request, $searchable);
        self::applySort($query, $request, $sortable, $defaultSort, $defaultDirection);

        $perPage = (int) $request->integer('per_page', 25);

        if (! in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = 25;
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * The state the front end needs to draw the table controls.
     *
     * @param  list<string>  $sortable
     * @return array{search: string, sort: string, direction: string, per_page: int}
     */
    public static function state(
        Request $request,
        array $sortable = [],
        string $defaultSort = 'id',
        string $defaultDirection = 'asc',
    ): array {
        $sort = (string) $request->string('sort', $defaultSort);

        if (! in_array($sort, $sortable, true)) {
            $sort = $defaultSort;
        }

        $direction = strtolower((string) $request->string('direction', $defaultDirection));

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaultDirection;
        }

        $perPage = (int) $request->integer('per_page', 25);

        return [
            'search' => trim((string) $request->string('search', '')),
            'sort' => $sort,
            'direction' => $direction,
            'per_page' => in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 25,
        ];
    }

    /**
     * @param  Builder<covariant Model>  $query
     * @param  list<string>  $searchable
     */
    private static function applySearch(Builder $query, Request $request, array $searchable): void
    {
        $search = trim((string) $request->string('search', ''));

        if ($search === '' || $searchable === []) {
            return;
        }

        $query->where(function (Builder $builder) use ($search, $searchable) {
            foreach ($searchable as $column) {
                if (! Str::contains($column, '.')) {
                    $builder->orWhere($builder->qualifyColumn($column), 'like', '%'.$search.'%');

                    continue;
                }

                [$relation, $relatedColumn] = explode('.', $column, 2);

                $builder->orWhereHas($relation, function (Builder $related) use ($relatedColumn, $search) {
                    $related->where($related->qualifyColumn($relatedColumn), 'like', '%'.$search.'%');
                });
            }
        });
    }

    /**
     * Sorting only accepts plain columns of the queried table. To sort by a
     * related table, the caller joins it and whitelists the alias.
     *
     * @param  Builder<covariant Model>  $query
     * @param  list<string>  $sortable
     */
    private static function applySort(
        Builder $query,
        Request $request,
        array $sortable,
        string $defaultSort,
        string $defaultDirection,
    ): void {
        $state = self::state($request, $sortable, $defaultSort, $defaultDirection);

        $query->orderBy(
            Str::contains($state['sort'], '.') ? $state['sort'] : $query->qualifyColumn($state['sort']),
            $state['direction'],
        );
    }
}
