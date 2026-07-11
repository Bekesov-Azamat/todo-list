<?php

namespace Tests\Feature\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TaskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_task(): void
    {
        $owner = User::factory()->create();

        $task = Task::factory()
            ->for($owner)
            ->create([
                'title' => 'Private task',
            ]);

        $this->actingAs($owner, 'web');

        $this
            ->getJson("/api/tasks/{$task->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath(
                'data.title',
                'Private task',
            );
    }

    public function test_another_user_cannot_access_or_mutate_task(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();

        $task = Task::factory()
            ->for($owner)
            ->create([
                'title' => 'Owner task',
                'status' => TaskStatus::Pending,
            ]);

        $this->actingAs($anotherUser, 'web');

        $this
            ->getJson("/api/tasks/{$task->id}")
            ->assertForbidden();

        $this
            ->patchJson("/api/tasks/{$task->id}", [
                'status' => TaskStatus::Completed->value,
            ])
            ->assertForbidden();

        $this
            ->deleteJson("/api/tasks/{$task->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Owner task',
            'status' => TaskStatus::Pending->value,
        ]);
    }
}
