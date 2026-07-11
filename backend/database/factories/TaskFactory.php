<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * @var class-string<Task>
     */
    protected $model = Task::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'due_date' => fake()
                ->optional()
                ->dateTimeBetween('now', '+3 months'),
            'status' => TaskStatus::Pending,
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => [
            'status' => TaskStatus::InProgress,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => TaskStatus::Completed,
        ]);
    }
}
