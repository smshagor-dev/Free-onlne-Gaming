<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class OauthRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_and_facebook_redirect_routes_use_socialite_without_real_provider_calls(): void
    {
        $googleProvider = Mockery::mock();
        $googleProvider->shouldReceive('redirect')->once()->andReturn(redirect('/oauth/google'));
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($googleProvider);

        $this->get(route('google.login'))->assertRedirect('/oauth/google');

        $facebookProvider = Mockery::mock();
        $facebookProvider->shouldReceive('redirect')->once()->andReturn(redirect('/oauth/facebook'));
        Socialite::shouldReceive('driver')->once()->with('facebook')->andReturn($facebookProvider);

        $this->get(route('facebook.login'))->assertRedirect('/oauth/facebook');
    }
}
