<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_register_and_becomes_authenticated(): void
    {
        $response = $this
            ->withHeaders($this->statefulHeaders())
            ->postJson('/api/auth/register', [
                'name' => '  Azamat Bekesov  ',
                'email' => 'AZAMAT@EXAMPLE.COM',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Azamat Bekesov')
            ->assertJsonPath('data.email', 'azamat@example.com');

        $user = User::query()
            ->where('email', 'azamat@example.com')
            ->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('StrongPass123', $user->password));
    }

    public function test_registration_rejects_duplicate_email_case_insensitively(): void
    {
        User::factory()->create([
            'email' => 'azamat@example.com',
        ]);

        $this
            ->withHeaders($this->statefulHeaders())
            ->postJson('/api/auth/register', [
                'name' => 'Another User',
                'email' => 'AZAMAT@EXAMPLE.COM',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_registration_rejects_weak_password(): void
    {
        $this
            ->withHeaders($this->statefulHeaders())
            ->postJson('/api/auth/register', [
                'name' => 'Azamat Bekesov',
                'email' => 'azamat@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');

        $this->assertGuest();
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
}
