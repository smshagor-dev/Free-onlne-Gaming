<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post(route('login'), [
                'login' => 'missing-rate-user',
                'password' => 'wrong-password',
            ])->assertRedirect('/login');
        }

        $this->from('/login')->post(route('login'), [
            'login' => 'missing-rate-user',
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('login');
    }

    public function test_redirect_to_rejects_external_urls_after_login(): void
    {
        User::factory()->create([
            'email' => 'safe-login@example.com',
            'username' => 'safe-login',
            'password' => Hash::make('secret-password'),
            'is_verified' => true,
            'last_login_at' => now(),
        ]);

        $this->post(route('login'), [
            'login' => 'safe-login@example.com',
            'password' => 'secret-password',
            'redirect_to' => 'https://evil.example/phish',
        ])->assertRedirect('/');
    }

    public function test_financial_actions_require_authentication(): void
    {
        $this->post(route('user.points.convert'), [
            'points' => 100,
            'conversion_type' => 'balance',
        ])->assertRedirect('/login');
    }
}
