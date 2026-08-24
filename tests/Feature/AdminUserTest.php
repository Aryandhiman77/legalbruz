<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_and_filter_registered_users(): void
    {
        $admin = Admin::create([
            'name' => 'User Admin',
            'email' => 'user-admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $verified = User::create([
            'name' => 'Verified Customer',
            'email' => 'verified@example.com',
            'mobile' => '9876543210',
            'password' => bcrypt('password'),
        ]);
        $verified->forceFill(['email_verified_at' => now()])->save();

        User::create([
            'name' => 'Pending Customer',
            'email' => 'pending@example.com',
            'mobile' => '9123456780',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Registered Users')
            ->assertSee('Verified Customer')
            ->assertSee('Pending Customer')
            ->assertSee('name="search"', false)
            ->assertSee('name="status"', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index', ['search' => '9876543210', 'status' => 'verified']))
            ->assertOk()
            ->assertSee('Verified Customer')
            ->assertDontSee('Pending Customer');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.users.index', ['status' => 'unverified']))
            ->assertOk()
            ->assertSee('Pending Customer')
            ->assertDontSee('Verified Customer');
    }
}
