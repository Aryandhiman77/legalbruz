<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use App\Models\User;
use App\Support\TrademarkWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminClientPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_read_only_client_dashboard_and_action_center_from_stage_actions(): void
    {
        $admin = Admin::create([
            'name' => 'Preview Admin',
            'email' => 'preview-admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $client = User::factory()->create([
            'name' => 'Current Client',
            'email' => 'current-client@example.com',
        ]);
        $application = Application::create([
            'user_id' => $client->id,
            'type' => 'trademark',
            'entity_type' => 'individual',
            'applicant_name' => 'Current Client',
            'phone' => '9876543210',
            'email' => $client->email,
            'brand_name' => 'Preview Mark',
            'status' => 'pending_admin',
            'service_status' => TrademarkWorkflow::ONBOARDING_PENDING,
        ]);
        $originalUpdatedAt = $application->updated_at;

        $this->actingAs($admin, 'admin')
            ->get(route('admin.view-application', $application->id))
            ->assertOk()
            ->assertSee('View Client Dashboard')
            ->assertSee('View Client Action Center')
            ->assertSee(route('admin.application.client-dashboard', $application->id), false)
            ->assertSee(route('admin.application.client-action-center', $application->id), false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.application.client-dashboard', $application->id))
            ->assertOk()
            ->assertSee('Read-only client preview')
            ->assertSee('Welcome, Current Client!')
            ->assertSee('Preview Mark')
            ->assertSee(route('admin.application.client-action-center', $application->id), false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.application.client-action-center', [
                'id' => $application->id,
                'stage_action' => 1,
            ]))
            ->assertOk()
            ->assertSee('Read-only client action center')
            ->assertSee('Inputs and client actions are disabled')
            ->assertSee('Action Center')
            ->assertSee('action="#"', false);

        $application->refresh();

        $this->assertTrue($application->updated_at->equalTo($originalUpdatedAt));
        $this->assertSame(TrademarkWorkflow::ONBOARDING_PENDING, $application->service_status);
    }

    public function test_client_preview_routes_require_admin_authentication(): void
    {
        $client = User::factory()->create();
        $application = Application::create([
            'user_id' => $client->id,
            'type' => 'trademark',
            'entity_type' => 'individual',
            'applicant_name' => $client->name,
            'phone' => '9876543210',
            'email' => $client->email,
            'brand_name' => 'Protected Preview Mark',
            'status' => 'pending_admin',
            'service_status' => TrademarkWorkflow::UNDER_REVIEW,
        ]);

        $this->get(route('admin.application.client-dashboard', $application->id))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.application.client-action-center', $application->id))
            ->assertRedirect(route('admin.login'));
    }

    public function test_the_existing_client_action_center_remains_interactive(): void
    {
        $client = User::factory()->create([
            'name' => 'Signed In Client',
            'email' => 'signed-in-client@example.com',
        ]);
        $application = Application::create([
            'user_id' => $client->id,
            'type' => 'trademark',
            'entity_type' => 'individual',
            'applicant_name' => $client->name,
            'phone' => '9876543210',
            'email' => $client->email,
            'brand_name' => 'Interactive Mark',
            'status' => 'approved',
            'service_status' => TrademarkWorkflow::ONBOARDING_PENDING,
        ]);

        $this->actingAs($client)
            ->get(route('trademark.status', [
                'id' => $application->id,
                'stage_action' => 1,
            ]))
            ->assertOk()
            ->assertDontSee('Read-only client action center')
            ->assertSee('action="'.route('workflow.onboarding.submit', $application->id).'"', false)
            ->assertDontSee('action="#"', false);
    }
}
