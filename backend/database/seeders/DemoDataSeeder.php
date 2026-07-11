<?php

namespace Database\Seeders;

use App\Enums\TaskStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'name' => 'Demo Administrator',
                'password' => 'Password123',
                'role' => UserRole::Admin,
                'email_verified_at' => now(),
            ],
        );

        $user = User::query()->updateOrCreate(
            [
                'email' => 'user@example.com',
            ],
            [
                'name' => 'Demo User',
                'password' => 'Password123',
                'role' => UserRole::User,
                'email_verified_at' => now(),
            ],
        );

        $adminTasks = [
            [
                'title' => 'Review all team tasks',
                'description' => 'Check ownership, status, and deadlines.',
                'status' => TaskStatus::InProgress,
                'due_date' => today()->addDay(),
            ],
            [
                'title' => 'Prepare release checklist',
                'description' => 'Confirm tests, migrations, and documentation.',
                'status' => TaskStatus::Pending,
                'due_date' => today()->addDays(3),
            ],
            [
                'title' => 'Archive completed reports',
                'description' => 'Move finished reports into the archive.',
                'status' => TaskStatus::Completed,
                'due_date' => today()->subDay(),
            ],
            [
                'title' => 'Update access policy',
                'description' => null,
                'status' => TaskStatus::Pending,
                'due_date' => null,
            ],
        ];

        $userTasks = [
            [
                'title' => 'Finish API documentation',
                'description' => 'Describe authentication and task endpoints.',
                'status' => TaskStatus::InProgress,
                'due_date' => today()->addDays(2),
            ],
            [
                'title' => 'Add dashboard tests',
                'description' => 'Cover filtering and pagination scenarios.',
                'status' => TaskStatus::Pending,
                'due_date' => today()->addDays(4),
            ],
            [
                'title' => 'Fix validation edge case',
                'description' => 'Verify invalid query parameters return 422.',
                'status' => TaskStatus::Completed,
                'due_date' => today()->subDay(),
            ],
            [
                'title' => 'Review pull request',
                'description' => 'Check the latest backend changes.',
                'status' => TaskStatus::Pending,
                'due_date' => today(),
            ],
            [
                'title' => 'Refine empty state copy',
                'description' => null,
                'status' => TaskStatus::Completed,
                'due_date' => null,
            ],
            [
                'title' => 'Verify mobile layout',
                'description' => 'Check forms and task cards on a narrow screen.',
                'status' => TaskStatus::Pending,
                'due_date' => today()->addDays(7),
            ],
            [
                'title' => 'Prepare demo screenshots',
                'description' => 'Capture user and administrator dashboards.',
                'status' => TaskStatus::InProgress,
                'due_date' => today()->addDays(5),
            ],
        ];

        foreach ($adminTasks as $task) {
            $admin->tasks()->updateOrCreate(
                [
                    'title' => $task['title'],
                ],
                $task,
            );
        }

        foreach ($userTasks as $task) {
            $user->tasks()->updateOrCreate(
                [
                    'title' => $task['title'],
                ],
                $task,
            );
        }
    }
}
