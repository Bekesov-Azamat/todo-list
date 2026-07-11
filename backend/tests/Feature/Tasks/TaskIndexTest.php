<?php

namespace Tests\Feature\Tasks;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TaskIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_tasks(): void
    {
        $this
            ->getJson('/api/tasks')
            ->assertUnauthorized();
    }

    public function test_user_receives_only_owned_tasks_in_newest_order(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $olderTask = Task::factory()
            ->for($user)
            ->create([
                'title' => 'Older owned task',
                'created_at' => now()->subDay(),
            ]);

        $newerTask = Task::factory()
            ->for($user)
            ->create([
                'title' => 'Newer owned task',
                'created_at' => now(),
            ]);

        Task::factory()
            ->for($anotherUser)
            ->create([
                'title' => 'Foreign task',
            ]);

        $this->actingAs($user, 'web');

        $this
            ->getJson('/api/tasks')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newerTask->id)
            ->assertJsonPath('data.1.id', $olderTask->id)
            ->assertJsonPath('meta.total', 2)
            ->assertJsonMissing([
                'title' => 'Foreign task',
            ]);
    }

    public function test_user_can_filter_tasks_by_status_and_search(): void
    {
        $user = User::factory()->create();

        Task::factory()
            ->for($user)
            ->completed()
            ->create([
                'title' => 'Prepare sprint report',
            ]);

        Task::factory()
            ->for($user)
            ->create([
                'title' => 'Prepare pending report',
            ]);

        Task::factory()
            ->for($user)
            ->completed()
            ->create([
                'title' => 'Clean the workspace',
            ]);

        $this->actingAs($user, 'web');

        $this
            ->getJson(
                '/api/tasks?status=completed&search=report',
            )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.title',
                'Prepare sprint report',
            )
            ->assertJsonPath(
                'data.0.status',
                TaskStatus::Completed->value,
            );
    }

    public function test_user_can_sort_by_due_date_and_paginate(): void
    {
        $user = User::factory()->create();

        Task::factory()->for($user)->create([
            'title' => 'No deadline',
            'due_date' => null,
        ]);

        Task::factory()->for($user)->create([
            'title' => 'Later deadline',
            'due_date' => '2026-09-20',
        ]);

        Task::factory()->for($user)->create([
            'title' => 'Nearest deadline',
            'due_date' => '2026-08-10',
        ]);

        $this->actingAs($user, 'web');

        $this
            ->getJson(
                '/api/tasks?sort=due_date_asc&per_page=2',
            )
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath(
                'data.0.title',
                'Nearest deadline',
            )
            ->assertJsonPath(
                'data.1.title',
                'Later deadline',
            )
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 3);
    }

    public function test_user_can_sort_tasks_by_domain_status(): void
    {
        $user = User::factory()->create();

        Task::factory()
            ->for($user)
            ->completed()
            ->create([
                'title' => 'Completed task',
            ]);

        Task::factory()
            ->for($user)
            ->create([
                'title' => 'Pending task',
            ]);

        Task::factory()
            ->for($user)
            ->inProgress()
            ->create([
                'title' => 'In progress task',
            ]);

        $this->actingAs($user, 'web');

        $this
            ->getJson('/api/tasks?sort=status_asc')
            ->assertOk()
            ->assertJsonPath(
                'data.0.status',
                TaskStatus::Pending->value,
            )
            ->assertJsonPath(
                'data.1.status',
                TaskStatus::InProgress->value,
            )
            ->assertJsonPath(
                'data.2.status',
                TaskStatus::Completed->value,
            );
    }
}
