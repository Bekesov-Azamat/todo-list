<?php

namespace Tests\Feature\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TaskMutationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_owned_task(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $this->actingAs($user, 'web');

        $this
            ->postJson('/api/tasks', [
                'title' => '  Ship Sprint 5  ',
                'description' => '  Contract is aligned.  ',
                'due_date' => '2026-08-20',
                'status' => TaskStatus::InProgress->value,
                'user_id' => $anotherUser->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Ship Sprint 5')
            ->assertJsonPath(
                'data.description',
                'Contract is aligned.',
            )
            ->assertJsonPath('data.due_date', '2026-08-20')
            ->assertJsonPath(
                'data.status',
                TaskStatus::InProgress->value,
            );

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Ship Sprint 5',
            'description' => 'Contract is aligned.',
            'status' => TaskStatus::InProgress->value,
        ]);

        $task = Task::query()
            ->where('user_id', $user->id)
            ->where('title', 'Ship Sprint 5')
            ->firstOrFail();

        $this->assertSame(
            '2026-08-20',
            $task->due_date?->toDateString(),
        );

        $this->assertDatabaseMissing('tasks', [
            'user_id' => $anotherUser->id,
            'title' => 'Ship Sprint 5',
        ]);
    }

    public function test_create_rejects_invalid_task_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web');

        $this
            ->postJson('/api/tasks', [
                'title' => 'ab',
                'description' => str_repeat('a', 5001),
                'due_date' => '20/08/2026',
                'status' => 'done',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'description',
                'due_date',
                'status',
            ]);

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_owner_can_partially_update_task(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()
            ->for($user)
            ->create([
                'title' => 'Initial title',
                'description' => 'Initial description',
                'due_date' => '2026-08-10',
                'status' => TaskStatus::Pending,
            ]);

        $this->actingAs($user, 'web');

        $this
            ->patchJson("/api/tasks/{$task->id}", [
                'title' => '  Updated title  ',
                'description' => null,
                'due_date' => null,
                'status' => TaskStatus::Completed->value,
            ])
            ->assertOk()
            ->assertJsonPath(
                'data.title',
                'Updated title',
            )
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.due_date', null)
            ->assertJsonPath(
                'data.status',
                TaskStatus::Completed->value,
            );

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated title',
            'description' => null,
            'due_date' => null,
            'status' => TaskStatus::Completed->value,
        ]);
    }

    public function test_update_requires_at_least_one_task_field(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user, 'web');

        $this
            ->patchJson("/api/tasks/{$task->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('task');
    }

    public function test_owner_can_delete_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->for($user)->create();

        $this->actingAs($user, 'web');

        $this
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
