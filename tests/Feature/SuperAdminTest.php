<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Inventory;
use App\Models\BorrowRelease;
use App\Models\Request as SupplyRequest;
use App\Models\UserLoginEvent;
use App\Models\User;
use App\Notifications\AccountSetupNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_has_a_dedicated_dashboard_and_activity_log_page(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($superAdmin)
            ->get('/')
            ->assertRedirect(route('super-admin.dashboard'));

        $this->actingAs($superAdmin)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Super Admin Dashboard')
            ->assertSee('Activity Logs');

        $this->actingAs($superAdmin)
            ->get(route('super-admin.activity-logs'))
            ->assertOk()
            ->assertSee('System Activity Logs');
    }

    public function test_non_super_admins_cannot_open_super_admin_pages(): void
    {
        foreach (['admin', 'staff', 'user'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get(route('super-admin.dashboard'))
                ->assertForbidden();

            $this->get(route('super-admin.activity-logs.users.show', User::factory()->create(['role' => 'staff'])))
                ->assertForbidden();
        }
    }

    public function test_both_activity_log_tabs_load_and_keep_filter_query_strings(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $actor = User::factory()->create(['role' => 'staff']);
        ActivityLog::create([
            'actor_id' => $actor->id,
            'event_type' => 'created',
            'subject_type' => User::class,
            'subject_id' => (string) $actor->id,
            'properties' => ['new' => ['name' => $actor->name, 'email' => $actor->email]],
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.activity-logs', ['tab' => 'recent', 'date_from' => now()->toDateString()]))
            ->assertOk()
            ->assertSee('All / Recent Activity')
            ->assertSee('From date');

        $this->get(route('super-admin.activity-logs', ['tab' => 'users', 'search' => $actor->email]))
            ->assertOk()
            ->assertSee('By User')
            ->assertSee($actor->name)
            ->assertSee($actor->email)
            ->assertSee('1');
    }

    public function test_user_activity_page_shows_only_that_users_logs_and_login_history(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $firstUser = User::factory()->create(['role' => 'staff']);
        $otherUser = User::factory()->create(['role' => 'delivery_personnel']);

        ActivityLog::create([
            'actor_id' => $firstUser->id,
            'event_type' => 'updated',
            'subject_type' => User::class,
            'subject_id' => (string) $firstUser->id,
            'properties' => ['old' => ['role' => 'user'], 'new' => ['role' => 'staff']],
        ]);
        ActivityLog::create([
            'actor_id' => $otherUser->id,
            'event_type' => 'deleted',
            'subject_type' => User::class,
            'subject_id' => (string) $otherUser->id,
            'properties' => ['old' => ['name' => 'Other Account Secret']],
        ]);
        UserLoginEvent::create([
            'user_id' => $firstUser->id,
            'email' => $firstUser->email,
            'succeeded' => false,
            'ip_address' => '192.0.2.10',
            'user_agent' => 'Test Browser',
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.activity-logs.users.show', $firstUser))
            ->assertOk()
            ->assertSee($firstUser->name)
            ->assertSee('Activity Today')
            ->assertSee('Login History')
            ->assertSee('192.0.2.10')
            ->assertDontSee('Other Account Secret');
    }

    public function test_super_admin_dashboard_shows_role_breakdown_recent_users_and_admin_metrics(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
            'last_login_at' => now()->subMinute(),
        ]);
        User::factory()->create(['role' => 'delivery_personnel', 'is_active' => false]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.dashboard'))
            ->assertOk()
            ->assertSee('Users by Role')
            ->assertSee('Recently Active Users')
            ->assertSee($staff->name)
            ->assertSee('Delivery Personnel')
            ->assertSee('Low Stock Alerts')
            ->assertSee('Activity Logs');
    }

    public function test_successful_and_failed_web_logins_are_recorded_and_inactive_accounts_are_blocked(): void
    {
        $user = User::factory()->create([
            'email' => 'login-history@example.test',
            'password' => Hash::make('correct-password'),
        ]);

        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertDatabaseHas('user_login_events', [
            'user_id' => $user->id,
            'succeeded' => true,
        ]);

        auth()->logout();
        $this->post('/login', ['email' => $user->email, 'password' => 'incorrect-password'])
            ->assertSessionHasErrors('email');
        $this->assertDatabaseHas('user_login_events', [
            'user_id' => $user->id,
            'succeeded' => false,
        ]);

        $user->update(['is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'correct-password'])
            ->assertSessionHasErrors('email');
    }

    public function test_deactivation_revokes_api_tokens_and_force_logout_invalidates_web_sessions(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $target = User::factory()->create(['role' => 'staff']);
        $target->createToken('test-device');

        $this->actingAs($superAdmin)
            ->post(route('super-admin.users.status', $target), ['is_active' => 0])
            ->assertRedirect();

        $this->assertFalse($target->fresh()->is_active);
        $this->assertSame(0, $target->tokens()->count());

        $target->update(['is_active' => true]);
        $oldVersion = $target->fresh()->auth_version;

        $this->post(route('super-admin.users.force-logout', $target))
            ->assertRedirect();

        $this->assertSame($oldVersion + 1, $target->fresh()->auth_version);
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $superAdmin->id,
            'event_type' => 'security_action',
            'subject_id' => (string) $target->id,
        ]);
    }

    public function test_password_reset_control_sends_link_and_revokes_existing_sessions_and_tokens(): void
    {
        Notification::fake();
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $target = User::factory()->create(['role' => 'staff']);
        $target->createToken('test-device');

        $this->actingAs($superAdmin)
            ->post(route('super-admin.users.password-reset', $target))
            ->assertRedirect()
            ->assertSessionHas('success');

        Notification::assertSentTo($target, AccountSetupNotification::class);
        $this->assertSame(0, $target->tokens()->count());
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => $superAdmin->id,
            'event_type' => 'password_reset_link_sent',
            'subject_id' => (string) $target->id,
        ]);
    }

    public function test_super_admin_can_use_existing_admin_pages_without_changing_admin_access(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($superAdmin)
            ->get('/admin/dashboard')
            ->assertOk();

        $this->actingAs($superAdmin)
            ->get(route('users.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_super_admin_activity_logs_record_actor_changes_and_exclude_passwords(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin);

        $user = User::factory()->create([
            'role' => 'staff',
            'password' => 'sensitive-test-password',
        ]);
        $user->update(['role' => 'admin']);

        $createdLog = ActivityLog::where('subject_type', User::class)
            ->where('subject_id', (string) $user->id)
            ->where('event_type', 'created')
            ->latest('id')
            ->firstOrFail();
        $updatedLog = ActivityLog::where('subject_type', User::class)
            ->where('subject_id', (string) $user->id)
            ->where('event_type', 'updated')
            ->latest('id')
            ->firstOrFail();

        $this->assertSame($superAdmin->id, $createdLog->actor_id);
        $this->assertSame($superAdmin->id, $updatedLog->actor_id);
        $this->assertSame('staff', $createdLog->properties['new']['role']);
        $this->assertArrayNotHasKey('password', $createdLog->properties['new']);
        $this->assertSame('staff', $updatedLog->properties['old']['role']);
        $this->assertSame('admin', $updatedLog->properties['new']['role']);
        $this->assertStringNotContainsString('sensitive-test-password', json_encode($createdLog->properties));
    }

    public function test_activity_log_page_shows_a_human_summary_and_field_diff_instead_of_raw_json(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $inventory = Inventory::create([
            'name' => 'Rice',
            'category' => 'Food',
            'sku' => 'RICE-001',
            'type' => 'Consumable',
            'quantity' => 150,
            'storage_location' => 'Warehouse A',
        ]);
        ActivityLog::create([
            'actor_id' => $superAdmin->id,
            'event_type' => 'created',
            'subject_type' => \App\Models\BorrowRelease::class,
            'subject_id' => '42',
            'properties' => [
                'new' => [
                    'inventory_id' => $inventory->id,
                    'quantity' => 150,
                    'unit' => 'Sack',
                    'location' => 'Hall',
                    'idempotency_key' => 'private-idempotency-key',
                    'created_at' => now()->toDateTimeString(),
                ],
            ],
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.activity-logs'))
            ->assertOk()
            ->assertSee('Released 150 Sack of Rice (SKU RICE-001) to Hall')
            ->assertSee('Quantity')
            ->assertSee('150')
            ->assertSee('Destination')
            ->assertSee('Hall')
            ->assertDontSee('"new":')
            ->assertDontSee('private-idempotency-key');
    }

    public function test_updated_activity_log_presents_old_and_new_values_as_a_field_diff(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        ActivityLog::create([
            'actor_id' => $superAdmin->id,
            'event_type' => 'updated',
            'subject_type' => User::class,
            'subject_id' => '99',
            'properties' => [
                'old' => ['role' => 'staff', 'email' => 'old@example.test'],
                'new' => ['role' => 'admin', 'email' => 'new@example.test'],
            ],
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.activity-logs'))
            ->assertOk()
            ->assertSee('Updated user account')
            ->assertSee('Role')
            ->assertSee('Staff')
            ->assertSee('Admin')
            ->assertSee('old@example.test')
            ->assertSee('new@example.test')
            ->assertDontSee('"old":');
    }

    public function test_regular_admins_cannot_view_or_modify_super_admin_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertDontSee($superAdmin->email);

        $this->actingAs($admin)
            ->get(route('users.edit', $superAdmin->id))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('users.destroy', $superAdmin->id))
            ->assertForbidden();

        $this->assertDatabaseHas('users', [
            'id' => $superAdmin->id,
            'role' => 'super_admin',
        ]);
    }

    public function test_initial_super_admin_can_be_created_only_with_a_confirmed_strong_password(): void
    {
        $this->artisan('super-admin:create')
            ->expectsQuestion('Full name', 'Primary Super Admin')
            ->expectsQuestion('Email address', 'superadmin@example.test')
            ->expectsQuestion('Password (minimum 12 characters)', 'a-strong-password-123')
            ->expectsQuestion('Confirm password', 'a-strong-password-123')
            ->expectsOutputToContain('Super Admin account created')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'superadmin@example.test',
            'role' => 'super_admin',
        ]);

        $this->artisan('super-admin:create')
            ->expectsOutputToContain('A Super Admin account already exists')
            ->assertExitCode(1);
    }

    public function test_super_admin_bootstrap_fails_before_prompting_when_activity_log_migration_is_pending(): void
    {
        Schema::drop('activity_logs');

        $this->artisan('super-admin:create')
            ->expectsOutputToContain('Run "php artisan migrate"')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', [
            'role' => 'super_admin',
        ]);
    }
}
