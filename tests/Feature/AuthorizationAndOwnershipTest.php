<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\LastPlay;
use App\Models\User;
use App\Models\UserKycSubmission;
use App\Models\Admin\KycField;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthorizationAndOwnershipTest extends TestCase
{
    use RefreshDatabase;



    public function test_removed_casino_endpoints_are_not_available(): void
    {
        foreach (['/casino', '/casino/play', '/api/casino/player', '/user/paid-game/favourite', '/admin/casino', '/user/deposit', '/user/withdrew', '/user/points/convert', '/user/bonus/claim/welcome', '/games/deals', '/api/games/deals'] as $url) {
            $this->get($url)->assertNotFound();
            $this->post($url)->assertNotFound();
        }
    }

    public function test_private_files_are_available_to_owners_and_admins_only(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('kyc_files/identity.pdf', 'private test file');

        $owner = $this->user();
        $other = $this->user();
        $field = KycField::create(['title' => 'Document', 'input_type' => 'file', 'is_required' => true]);
        UserKycSubmission::create([
            'user_id' => $owner->id,
            'kyc_field_id' => $field->id,
            'value' => 'kyc_files/identity.pdf',
            'status' => 'pending',
        ]);

        $this->actingAs($owner)
            ->get(route('files.private', ['path' => 'kyc_files/identity.pdf']))
            ->assertOk();

        $this->actingAs($other)
            ->get(route('files.private', ['path' => 'kyc_files/identity.pdf']))
            ->assertForbidden();

        $admin = Admin::create([
            'name' => 'File Admin',
            'email' => 'file-admin@example.test',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($admin, 'admin')
            ->get(route('files.private', ['path' => 'kyc_files/identity.pdf']))
            ->assertOk();
    }

    public function test_admin_routes_and_post_only_mutations_reject_the_wrong_access_mode(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
        $this->get(route('user.notifications.markAllAsRead'))->assertMethodNotAllowed();
        $this->get(route('admin.lottary.draw', 1))->assertMethodNotAllowed();
    }

    public function test_admin_login_and_logout_use_the_admin_guard_only(): void
    {
        $admin = Admin::create([
            'name' => 'Guard Admin',
            'email' => 'guard-admin@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');

        $this->post(route('admin.logout'))->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    private function user(): User
    {
        return User::factory()->create([
            'google2fa_status' => false,
            'is_banned' => false,
            'kyc_verified' => true,
            'date_of_birth' => '1990-01-01',
            'mobile_number' => '123456789',
        ]);
    }
}
