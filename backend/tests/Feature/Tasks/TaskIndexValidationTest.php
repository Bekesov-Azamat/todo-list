<?php

namespace Tests\Feature\Tasks;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskIndexValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_task_index_parameters_return_validation_errors(): void
    {
        $user = User::factory()->create();

        $query = http_build_query([
            'status' => 'archived',
            'search' => str_repeat('x', 256),
            'sort' => 'random',
            'per_page' => 100,
            'page' => 0,
        ]);

        $this
            ->actingAs($user)
            ->getJson("/api/tasks?{$query}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'status',
                'search',
                'sort',
                'per_page',
                'page',
            ]);
    }
}
