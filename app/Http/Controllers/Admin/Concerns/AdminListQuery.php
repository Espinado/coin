<?php

namespace App\Http\Controllers\Admin\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

trait AdminListQuery
{
    protected function adminSearchTerm(Request $request, string $param = 'q'): string
    {
        return trim($request->string($param)->toString());
    }

    protected function adminPerPage(Request $request, int $default = 20): int
    {
        $perPage = (int) $request->input('per_page', $default);

        return in_array($perPage, [10, 20, 50, 100], true) ? $perPage : $default;
    }

    protected function adminSortDirection(Request $request): string
    {
        return strtolower($request->string('dir')->toString()) === 'asc' ? 'asc' : 'desc';
    }

    /**
     * @param  array<string, string>  $allowed
     * @param  array<string, callable(Builder, string): void>  $customSorts
     */
    protected function adminApplySort(
        Request $request,
        Builder $query,
        array $allowed,
        string $defaultColumn,
        string $defaultDirection = 'desc',
        array $customSorts = [],
    ): void {
        $sort = $request->string('sort')->toString();
        $direction = $this->adminSortDirection($request);

        if ($sort !== '' && isset($customSorts[$sort])) {
            $customSorts[$sort]($query, $direction);
            $this->adminApplySortTiebreaker($query);

            return;
        }

        if ($sort !== '' && array_key_exists($sort, $allowed)) {
            $query->orderBy($allowed[$sort], $direction);
            $this->adminApplySortTiebreaker($query);

            return;
        }

        $query->orderBy($defaultColumn, $defaultDirection);
        $this->adminApplySortTiebreaker($query);
    }

    protected function adminOrderByRelatedUser(
        Builder $query,
        string $column,
        string $direction,
        string $foreignKey = 'user_id',
    ): void {
        $table = $query->getModel()->getTable();

        $query->orderBy(
            User::query()
                ->select($column)
                ->whereColumn('users.id', "{$table}.{$foreignKey}")
                ->limit(1),
            $direction,
        );
    }

    protected function adminPaginate(Builder $query, Request $request, int $defaultPerPage = 20): LengthAwarePaginator
    {
        return $query->paginate($this->adminPerPage($request, $defaultPerPage))->withQueryString();
    }

    /** @return array{search: string, status: string, sort: string, dir: string, perPage: int} */
    protected function adminListState(Request $request, int $defaultPerPage = 20): array
    {
        return [
            'search' => $this->adminSearchTerm($request),
            'status' => $request->string('status')->toString(),
            'sort' => $request->string('sort')->toString(),
            'dir' => $this->adminSortDirection($request),
            'perPage' => $this->adminPerPage($request, $defaultPerPage),
        ];
    }

    protected function adminApplySortTiebreaker(Builder $query): void
    {
        $model = $query->getModel();
        $keyName = $model->getKeyName();

        if (is_string($keyName) && $keyName !== '') {
            $query->orderByDesc($model->getTable().'.'.$keyName);
        }
    }
}
