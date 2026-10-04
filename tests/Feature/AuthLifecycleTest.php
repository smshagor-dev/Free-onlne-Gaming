<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_uses_mocked_location_lookup_and_starts_email_verification(): void
    {
        Http::fake([
            'ip-api.com/*' => Http::response(['country' => 'Testland', 'regionName' => 'North', 'city' => 'Spec City']),
        ]);
        Mail::fake();

        $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'new-user@example.test',
            'username' => 'new-user',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect(route('verify.form'));

        $user = User::where('email', 'new-user@example.test')->firstOrFail();
        $this->assertFalse($user->is_verified);
        $this->assertNotEmpty($user->verification_code);
        $this->assertSame('Testland', $user->user_country);
        Http::assertSentCount(1);
    }

    public function test_login_and_logout_manage_the_user_session(): void
    {
        $user = User::factory()->create([
            'email' => 'member@example.test',
            'username' => 'member',
            'password' => Hash::make('secret-password'),
            'is_verified' => true,
            'last_login_at' => now()->subDay(),
        ]);

        $this->post(route('login'), [
            'login' => $user->email,
            'password' => 'secret-password',
        ])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_verification_codes_and_resends_are_bound_to_the_authenticated_user(): void
    {
        Mail::fake();

        $owner = User::factory()->create([
            'is_verified' => false,
            'verification_code' => '111111',
        ]);
        $other = User::factory()->create([
            'is_verified' => false,
            'verification_code' => '222222',
        ]);

        $this->actingAs($owner)
            ->from(route('verify.form'))
            ->post(route('verify.code'), ['code' => $other->verification_code])
            ->assertRedirect(route('verify.form'))
            ->assertSessionHasErrors('code');

        $other->refresh();
        $this->assertFalse($other->is_verified);

        $this->actingAs($owner)
            ->post(route('verify.resend'), ['email' => $other->email])
            ->assertForbidden();

        $this->actingAs($owner)
            ->post(route('verify.code'), ['code' => $owner->verification_code])
            ->assertRedirect('/home');

        $owner->refresh();
        $this->assertTrue($owner->is_verified);
        $this->assertNull($owner->verification_code);
    }

    public function test_profile_email_and_password_updates_only_affect_the_authenticated_user(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email' => 'profile@example.test',
            'password' => Hash::make('old-password'),
            'is_verified' => true,
        ]);
        $other = User::factory()->create();

        $this->actingAs($user)
            ->put(route('user.profile.update'), [
                'name' => 'Updated Profile',
                'email' => 'updated-profile@example.test',
                'mobile' => '123456789',
                'date_of_birth' => '1990-01-01',
            ])->assertRedirect(route('verify.form'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'updated-profile@example.test',
            'is_verified' => 0,
        ]);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'email' => $other->email]);

        $this->actingAs($user)
            ->from(route('user.profile.edit'))
            ->post(route('user.profile.changePassword'), [
                'current_password' => 'old-password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ])->assertRedirect(route('user.profile.edit'));

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }
}
