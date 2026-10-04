<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_reset_lookup_sends_broker_token_without_enumeration_error(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'player@example.com',
            'username' => 'playername',
            'is_verified' => true,
        ]);

        $this->post(route('auth.searchUser'), ['identifier' => 'playername'])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);

        $this->post(route('auth.searchUser'), ['identifier' => 'missing-user'])
            ->assertRedirect()
            ->assertSessionHas('status')
            ->assertSessionDoesntHaveErrors('identifier');
    }

    public function test_password_can_be_reset_only_with_valid_broker_token(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => 'reset@example.com',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertRedirect('/home');

        $this->assertTrue(Hash::check('new-secret-password', $user->refresh()->password));
    }
}
