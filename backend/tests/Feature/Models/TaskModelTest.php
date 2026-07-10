<?php

namespace Tests\Feature\Models;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TaskModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_belongs_to_user_and_casts_completion_status(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()
            ->for($user)
            ->completed()
            ->create();

        $this->assertTrue($task->user->is($user));
        $this->assertTrue($task->is_completed);
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
