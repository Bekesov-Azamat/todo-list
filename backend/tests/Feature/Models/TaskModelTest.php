<?php

namespace Tests\Feature\Models;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_belongs_to_user_and_casts_domain_attributes(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()
            ->for($user)
            ->completed()
            ->create([
                'due_date' => '2026-08-15',
            ]);

        $this->assertTrue($task->user->is($user));
        $this->assertSame(
            TaskStatus::Completed,
            $task->status,
        );
        $this->assertSame(
            '2026-08-15',
            $task->due_date->toDateString(),
        );
    }

    public function test_user_exposes_owned_tasks_relation(): void
    {
        $user = User::factory()->create();

        Task::factory()
            ->count(2)
            ->for($user)
            ->create();

        Task::factory()->create();

        $this->assertCount(2, $user->tasks()->get());
    }

    public function test_deleting_user_cascades_to_owned_tasks(): void
    {
        $task = Task::factory()->create();
        $user = $task->user;

        $user->delete();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
