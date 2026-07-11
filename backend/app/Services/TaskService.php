<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

final class TaskService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(
        User $user,
        array $attributes,
    ): Task {
        return $user->tasks()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(
        Task $task,
        array $attributes,
    ): Task {
        $task->fill($attributes);
        $task->save();

        return $task->refresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
