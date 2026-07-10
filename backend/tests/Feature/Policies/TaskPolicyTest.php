<?php

namespace Tests\Feature\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TaskPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_and_create_tasks(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->can('viewAny', Task::class));
        $this->assertTrue($user->can('create', Task::class));
    }

    public function test_owner_can_view_update_and_delete_task(): void
    {
        $owner = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->assertTrue($owner->can('view', $task));
        $this->assertTrue($owner->can('update', $task));
        $this->assertTrue($owner->can('delete', $task));
    }

    public function test_another_user_cannot_view_update_or_delete_task(): void
    {
        $owner = User::factory()->create();
        $anotherUser = User::factory()->create();
        $task = Task::factory()->for($owner)->create();

        $this->assertFalse($anotherUser->can('view', $task));
        $this->assertFalse($anotherUser->can('update', $task));
        $this->assertFalse($anotherUser->can('delete', $task));
    }
}
