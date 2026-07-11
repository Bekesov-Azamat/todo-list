<?php

namespace Tests\Feature\Tasks;

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
                'title' => '  Ship Sprint 4  ',
                'description' => '  REST API is ready.  ',
                'is_completed' => true,
                'user_id' => $anotherUser->id,
            ])
            ->assertCreated()
            ->assertJsonPath('data.title', 'Ship Sprint 4')
            ->assertJsonPath(
                'data.description',
                'REST API is ready.',
            )
            ->assertJsonPath('data.is_completed', true);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'title' => 'Ship Sprint 4',
            'description' => 'REST API is ready.',
            'is_completed' => true,
        ]);

        $this->assertDatabaseMissing('tasks', [
            'user_id' => $anotherUser->id,
            'title' => 'Ship Sprint 4',
        ]);
    }

    public function test_create_rejects_invalid_task_data(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web');

        $this
            ->postJson('/api/tasks', [
                'title' => '   ',
                'description' => str_repeat('a', 5001),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'description',
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
                'is_completed' => false,
            ]);

        $this->actingAs($user, 'web');

        $this
            ->patchJson("/api/tasks/{$task->id}", [
                'title' => '  Updated title  ',
                'description' => null,
                'is_completed' => true,
            ])
            ->assertOk()
            ->assertJsonPath(
                'data.title',
                'Updated title',
            )
            ->assertJsonPath('data.description', null)
            ->assertJsonPath('data.is_completed', true);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated title',
            'description' => null,
            'is_completed' => true,
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
