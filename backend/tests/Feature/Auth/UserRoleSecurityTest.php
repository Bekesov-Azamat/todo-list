<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_cannot_escalate_user_role(): void
    {
        $response = $this
            ->withHeaders([
                'Origin' => 'http://localhost:3000',
                'Referer' => 'http://localhost:3000/',
            ])
            ->postJson('/api/auth/register', [
                'name' => 'Regular User',
                'email' => 'regular@example.com',
                'password' => 'Password123',
                'password_confirmation' => 'Password123',
                'role' => UserRole::Admin->value,
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.role',
                UserRole::User->value,
            );

        $user = User::query()
            ->where('email', 'regular@example.com')
            ->firstOrFail();

        $this->assertSame(
            UserRole::User,
            $user->role,
        );

        $this->assertFalse($user->isAdmin());
    }
}
