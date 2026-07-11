<?php

namespace Tests\Feature\Tasks;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTaskAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_receives_tasks_from_all_users(): void
    {
        $admin = User::factory()->admin()->create();
        $firstOwner = User::factory()->create();
        $secondOwner = User::factory()->create();

        $firstTask = Task::factory()
            ->for($firstOwner)
            ->create([
                'title' => 'First owner task',
            ]);

        $secondTask = Task::factory()
            ->for($secondOwner)
            ->create([
                'title' => 'Second owner task',
            ]);

        $response = $this
            ->actingAs($admin)
            ->getJson('/api/tasks?per_page=50');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $firstTask->id,
                'title' => 'First owner task',
            ])
            ->assertJsonFragment([
                'id' => $secondTask->id,
                'title' => 'Second owner task',
            ])
            ->assertJsonFragment([
                'owner' => [
                    'id' => $firstOwner->id,
                    'name' => $firstOwner->name,
                ],
            ])
            ->assertJsonFragment([
                'permissions' => [
                    'update' => true,
                    'delete' => true,
                ],
            ]);
    }

    public function test_admin_can_update_and_delete_another_users_task(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();

        $task = Task::factory()
            ->for($owner)
            ->create([
                'title' => 'Original title',
            ]);

        $this
            ->actingAs($admin)
            ->patchJson("/api/tasks/{$task->id}", [
                'title' => 'Updated by administrator',
            ])
            ->assertOk()
            ->assertJsonPath(
                'data.title',
                'Updated by administrator',
            )
            ->assertJsonPath('data.owner.id', $owner->id)
            ->assertJsonPath(
                'data.permissions.update',
                true,
            )
            ->assertJsonPath(
                'data.permissions.delete',
                true,
            );

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'user_id' => $owner->id,
            'title' => 'Updated by administrator',
        ]);

        $this
            ->actingAs($admin)
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
