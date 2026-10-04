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

    public function test_kyc_submission_is_owned_by_the_authenticated_user(): void
    {
        Mail::fake();
        $field = KycField::create([
            'title' => 'Identity number',
            'input_type' => 'text',
            'is_required' => true,
        ]);
        $user = $this->user();
        $other = $this->user();

        $this->actingAs($user)
            ->post(route('user.kyc.store'), ['kyc_'.$field->id => 'ABC-123'])
            ->assertRedirect(route('user.kyc.index'));

        $this->assertDatabaseHas('user_kyc_submissions', [
            'user_id' => $user->id,
            'kyc_field_id' => $field->id,
            'value' => 'ABC-123',
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('user_kyc_submissions', [
            'user_id' => $other->id,
            'kyc_field_id' => $field->id,
        ]);
    }

    public function test_casino_favourites_and_bookmarks_are_scoped_to_each_user(): void
    {
        $user = $this->user();
        $other = $this->user();

        $this->actingAs($user)
            ->postJson(route('user.game.toggleFavourite'), ['game_id' => 701])
            ->assertOk()
            ->assertJsonPath('is_active', true);

        $this->actingAs($other)
            ->postJson(route('user.game.toggleFavourite'), ['game_id' => 701])
            ->assertOk()
            ->assertJsonPath('is_active', true);

        $this->actingAs($user)
            ->postJson(route('user.game.toggleBookmark'), ['game_id' => 701])
            ->assertOk()
            ->assertJsonPath('is_active', true);

        $this->assertDatabaseHas('last_play', ['user_id' => $user->id, 'game_id' => 701, 'is_favourite' => 1, 'is_bookmark' => 1]);
        $this->assertDatabaseHas('last_play', ['user_id' => $other->id, 'game_id' => 701, 'is_favourite' => 1, 'is_bookmark' => 0]);
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
        $this->get(route('admin.casino.cache'))->assertMethodNotAllowed();
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
