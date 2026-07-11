<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_cookie_endpoint_is_available(): void
    {
        $this
            ->withHeaders($this->statefulHeaders())
            ->get('/sanctum/csrf-cookie')
            ->assertNoContent();
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'azamat@example.com',
            'password' => 'StrongPass123',
        ]);

        $this
            ->withHeaders($this->statefulHeaders())
            ->postJson('/api/auth/login', [
                'email' => 'AZAMAT@EXAMPLE.COM',
                'password' => 'StrongPass123',
                'remember' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'azamat@example.com');

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'azamat@example.com',
            'password' => 'StrongPass123',
        ]);

        $this
            ->withHeaders($this->statefulHeaders())
            ->postJson('/api/auth/login', [
                'email' => 'azamat@example.com',
                'password' => 'WrongPass123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_guest_cannot_access_current_user_endpoint(): void
    {
        $this
            ->withHeaders($this->statefulHeaders())
            ->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_access_current_user_endpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this
            ->withHeaders($this->statefulHeaders())
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_authenticated_user_can_log_out(): void
    {
        $user = User::factory()->create([
            'email' => 'logout@example.com',
            'password' => 'StrongPass123',
        ]);

        $this
            ->withHeaders($this->statefulHeaders())
            ->postJson('/api/auth/login', [
                'email' => 'logout@example.com',
                'password' => 'StrongPass123',
            ])
            ->assertOk();

        $this->assertAuthenticatedAs($user, 'web');

        $this
            ->withHeaders($this->statefulHeaders())
            ->postJson('/api/auth/logout')
            ->assertNoContent();

        $this->assertGuest('web');

        Auth::forgetGuards();

        $this
            ->withHeaders($this->statefulHeaders())
            ->getJson('/api/user')
            ->assertUnauthorized();
    }

    public function test_login_is_rate_limited_after_five_failed_attempts(): void
    {
        User::factory()->create([
            'email' => 'azamat@example.com',
            'password' => 'StrongPass123',
        ]);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this
                ->withHeaders($this->statefulHeaders())
                ->postJson('/api/auth/login', [
                    'email' => 'azamat@example.com',
                    'password' => 'WrongPass123',
                ])
                ->assertUnprocessable();
        }

        $this
            ->withHeaders($this->statefulHeaders())
            ->postJson('/api/auth/login', [
                'email' => 'azamat@example.com',
                'password' => 'WrongPass123',
            ])
            ->assertStatus(429);
    }

    /**
     * @return array<string, string>
     */
    private function statefulHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Origin' => 'http://localhost:3000',
            'Referer' => 'http://localhost:3000/',
        ];
    }

    public function test_browser_api_request_receives_json_unauthorized_response(): void
    {
        $this
            ->get('/api/user', [
                'Origin' => 'http://localhost:3000',
            ])
            ->assertUnauthorized()
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
