<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Application;
use App\Models\User;
use App\Support\TrademarkWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminTrademarkReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_move_a_submitted_application_into_review(): void
    {
        Mail::fake();

        $admin = Admin::create([
            'name' => 'Review Admin',
            'email' => 'review-admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $client = User::factory()->create();
        $application = Application::create([
            'user_id' => $client->id,
            'type' => 'trademark',
            'entity_type' => 'individual',
            'applicant_name' => $client->name,
            'phone' => '9876543210',
            'email' => $client->email,
            'brand_name' => 'Review Queue Mark',
            'status' => TrademarkWorkflow::APPLICATION_SUBMITTED,
            'service_status' => TrademarkWorkflow::APPLICATION_SUBMITTED,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.view-application', $application->id))
            ->assertOk()
            ->assertSee('Start Admin Review')
            ->assertSee(route('admin.start-review', $application->id), false);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.start-review', $application->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $application->refresh();

        $this->assertSame(TrademarkWorkflow::UNDER_REVIEW, $application->status);
        $this->assertSame(TrademarkWorkflow::UNDER_REVIEW, $application->service_status);
        $this->assertDatabaseHas('application_status_logs', [
            'application_id' => $application->id,
            'from_status' => TrademarkWorkflow::APPLICATION_SUBMITTED,
            'to_status' => TrademarkWorkflow::UNDER_REVIEW,
            'actor_type' => 'admin',
            'actor_id' => $admin->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $client->id,
            'type' => 'under_review',
            'title' => 'Application under review',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.view-application', $application->id))
            ->assertOk()
            ->assertDontSee('Start Admin Review')
            ->assertSee('Approval Note');
    }

    public function test_start_review_cannot_change_an_application_in_another_stage(): void
    {
        Mail::fake();

        $admin = Admin::create([
            'name' => 'Review Admin',
            'email' => 'second-review-admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $client = User::factory()->create();
        $application = Application::create([
            'user_id' => $client->id,
            'type' => 'trademark',
            'entity_type' => 'individual',
            'applicant_name' => $client->name,
            'phone' => '9876543210',
            'email' => $client->email,
            'brand_name' => 'Unchanged Mark',
            'status' => TrademarkWorkflow::ONBOARDING_PENDING,
            'service_status' => TrademarkWorkflow::ONBOARDING_PENDING,
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.start-review', $application->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame(TrademarkWorkflow::ONBOARDING_PENDING, $application->fresh()->current_status);
    }
}
