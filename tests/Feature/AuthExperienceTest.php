<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AuthExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::put('gamemonetize.embeddable.all', [], 60);
        Http::preventStrayRequests();
    }

    public function test_navigation_exposes_both_responsive_auth_dialogs_to_guests(): void
    {
        $this->get(route('games.viewIndex'))
            ->assertOk()
            ->assertSee('data-auth-open="login"', false)
            ->assertSee('data-auth-open="register"', false)
            ->assertSee('id="fg-auth-dialog"', false)
            ->assertSee('data-auth-panel="login"', false)
            ->assertSee('data-auth-panel="register"', false)
            ->assertSee('name="auth_context" value="login"', false)
            ->assertSee('name="auth_context" value="register"', false)
            ->assertSee('action="'.route('login').'"', false)
            ->assertSee('action="'.route('register').'"', false)
            ->assertDontSee('telegram-widget.js');
    }

    public function test_authentication_forms_render_with_correct_routes_and_mobile_ready_fields(): void
    {
        $this->get(route('login.form'))
            ->assertOk()
            ->assertSee('Sign in to FreeGames')
            ->assertSee('name="login"', false)
            ->assertSee('name="remember"', false)
            ->assertSee(route('auth.forgotPasswordForm'), false);

        $this->get(route('register.form'))
            ->assertOk()
            ->assertSee('Create your account')
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('data-country-select', false)
            ->assertSee('name="photo"', false)
            ->assertDontSee('telegram-widget.js');

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Admin sign in')
            ->assertSee('action="'.route('admin.login.submit').'"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false);

        $this->get(route('auth.forgotPasswordForm'))->assertOk()
            ->assertSee('action="'.route('auth.searchUser').'"', false);

        $this->get(route('password.request'))->assertOk()
            ->assertSee('action="'.route('password.email').'"', false);

        $this->get(route('password.reset', ['token' => 'demo-token']))->assertOk()
            ->assertSee('name="token"', false)
            ->assertSee('action="'.route('password.update').'"', false);
    }

    public function test_invalid_popup_login_preserves_context_without_flashing_password(): void
    {
        $this->from(route('games.viewIndex'))->post(route('login'), [
            'login' => 'wrong@example.test',
            'password' => 'not-the-password',
            'auth_context' => 'login',
        ])->assertRedirect(route('games.viewIndex'))->assertSessionHas('error');

        $this->get(route('games.viewIndex'))
            ->assertOk()
            ->assertSee('data-auto-open="login"', false)
            ->assertDontSee('not-the-password');
    }

    public function test_registration_errors_reopen_the_registration_modal(): void
    {
        $this->from(route('games.viewIndex'))->post(route('register'), [
            'auth_context' => 'register',
            'name' => '',
            'username' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertRedirect(route('games.viewIndex'))->assertSessionHasErrors();

        $this->get(route('games.viewIndex'))
            ->assertOk()->assertSee('data-auto-open="register"', false);
    }

    public function test_telegram_callback_is_retired_but_oauth_providers_remain_available(): void
    {
        $this->assertFalse(Route::has('telegram.callback'));
        $this->assertTrue(Route::has('google.login'));
        $this->assertTrue(Route::has('facebook.login'));
        $this->get('/auth/telegram/callback')->assertNotFound();
    }

    public function test_new_quick_account_password_is_hashed(): void
    {
        Http::fake(['ip-api.com/*' => Http::response(['country' => 'Testland'])]);

        $response = $this->post(route('quick.register'), []);
        $response->assertOk()->assertSee('Your account is ready.');

        preg_match('/id="quick-success-password" type="password" readonly value="([^"]+)"/', $response->getContent(), $matches);
        $this->assertCount(2, $matches);

        $user = User::orderByDesc('id')->firstOrFail();
        $this->assertNotSame($matches[1], $user->password);
        $this->assertTrue(Hash::check($matches[1], $user->password));
    }
}
