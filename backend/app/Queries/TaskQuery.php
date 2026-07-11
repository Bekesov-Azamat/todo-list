<?php

namespace App\Queries;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class TaskQuery
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<int, Task>
     */
    public function paginate(
        User $user,
        array $filters,
    ): LengthAwarePaginator {
        $query = $user->isAdmin()
            ? Task::query()
            : $user->tasks()->getQuery();

        $query->with('user');

        $this->applyStatus($query, $filters);
        $this->applySearch($query, $filters);
        $this->applySort($query, $filters);

        $perPage = isset($filters['per_page'])
            && is_numeric($filters['per_page'])
                ? (int) $filters['per_page']
                : 10;

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  Builder<Task>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyStatus(
        Builder $query,
        array $filters,
    ): void {
        $status = $filters['status'] ?? 'all';

        if (
            is_string($status)
            && in_array($status, TaskStatus::values(), true)
        ) {
            $query->where('status', $status);
        }
    }

    /**
     * @param  Builder<Task>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applySearch(
        Builder $query,
        array $filters,
    ): void {
        $search = $filters['search'] ?? null;

        if (! is_string($search) || $search === '') {
            return;
        }

        $query->where(
            function (Builder $query) use ($search): void {
                $query
                    ->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            },
        );
    }

    /**
     * @param  Builder<Task>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applySort(
        Builder $query,
        array $filters,
    ): void {
        $sort = is_string($filters['sort'] ?? null)
            ? $filters['sort']
            : 'newest';

        match ($sort) {
            'oldest' => $query
                ->orderBy('created_at')
                ->orderBy('id'),

            'due_date_asc' => $query
                ->orderByRaw(
                    'CASE WHEN due_date IS NULL THEN 1 ELSE 0 END',
                )
                ->orderBy('due_date')
                ->orderBy('id'),

            'due_date_desc' => $query
                ->orderByRaw(
                    'CASE WHEN due_date IS NULL THEN 1 ELSE 0 END',
                )
                ->orderByDesc('due_date')
                ->orderByDesc('id'),

            'status_asc' => $query
                ->orderByRaw(
                    "CASE status
                        WHEN 'pending' THEN 1
                        WHEN 'in_progress' THEN 2
                        WHEN 'completed' THEN 3
                        ELSE 4
                    END",
                )
                ->orderBy('id'),

            'status_desc' => $query
                ->orderByRaw(
                    "CASE status
                        WHEN 'completed' THEN 1
                        WHEN 'in_progress' THEN 2
                        WHEN 'pending' THEN 3
                        ELSE 4
                    END",
                )
                ->orderBy('id'),

            'title_asc' => $query
                ->orderBy('title')
                ->orderBy('id'),

            'title_desc' => $query
                ->orderByDesc('title')
                ->orderByDesc('id'),

            default => $query
                ->orderByDesc('created_at')
                ->orderByDesc('id'),
        };
    }
}
